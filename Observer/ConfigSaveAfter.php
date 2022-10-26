<?php
/**
 * Copyright (c) 2016-2022 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OnTap\MasterCard\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use OnTap\MasterCard\Gateway\Config\Config;
use OnTap\MasterCard\Gateway\Config\ConfigFactory;
use OnTap\MasterCard\Model\SelectedStore;
use Psr\Log\LoggerInterface;

class ConfigSaveAfter implements ObserverInterface
{
    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * @var CommandPoolInterface
     */
    protected $commandPool;

    /**
     * @var SelectedStore
     */
    protected $selectedStore;

    /**
     * @var string[]
     */
    protected $methods;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ConfigSaveAfter constructor.
     *
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param ManagerInterface $messageManager
     * @param ConfigFactory $configFactory
     * @param CommandPoolInterface $commandPool
     * @param SelectedStore $selectedStore
     * @param ScopeConfigInterface $config
     * @param LoggerInterface $logger
     * @param array $methods
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepository,
        ManagerInterface $messageManager,
        ConfigFactory $configFactory,
        CommandPoolInterface $commandPool,
        SelectedStore $selectedStore,
        ScopeConfigInterface $config,
        LoggerInterface $logger,
        $methods = []
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->groupRepository = $groupRepository;
        $this->messageManager = $messageManager;
        $this->configFactory = $configFactory;
        $this->commandPool = $commandPool;
        $this->selectedStore = $selectedStore;
        $this->config = $config;
        $this->logger = $logger;
        $this->methods = $methods;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getRequest();
        $configData = $observer->getData('configData');

        try {
            if (empty($configData['section'])) {
                return;
            }

            if ($configData['section'] !== 'payment') {
                return;
            }

            $websiteId = $request->getParam('website');
            $storeId = $request->getParam('store');

            if (empty($storeId) && !empty($websiteId)) {
                $website = $this->websiteRepository->getById($websiteId);
                $storeGroupId = $website->getDefaultGroupId();
                $group = $this->groupRepository->get($storeGroupId);
                $storeId = $group->getDefaultStoreId();
            }

            $this->selectedStore->setStoreId($storeId);

            foreach ($this->methods as $method => $label) {
                $config = $this->configFactory->create(['methodCode' => $method]);

                $isCertificate = $config->isCertificateAutherntification($storeId);
                $merchantId = $config->getMerchantId($storeId);
                $apiUrl = $config->getApiUrl($storeId);
                $enabled = "1" === $this->config->getValue(
                    sprintf('payment/%s/active', $method),
                    ($storeId !== null) ? ScopeInterface::SCOPE_STORE : ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    $storeId
                );

                if ($isCertificate) {
                    $sslCertPath = $config->getSSLCertificatePath($storeId);
                    $sslKeyPath = $config->getSSLKeyPath($storeId);
                    if (!$enabled || !$merchantId || !$sslCertPath || !$sslKeyPath || !$apiUrl) {
                        continue;
                    }
                } else {
                    $password = $config->getMerchantPassword($storeId);
                    if (!$enabled || !$merchantId || !$password || !$apiUrl) {
                        continue;
                    }
                }

                try {
                    $command = $this->commandPool->get(sprintf('check_gateway_%s', $method));
                    $command->execute([]);
                    $this->messageManager->addSuccessMessage(
                        __('"%1" test was successful.', __($label))
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addWarningMessage(
                        __(
                            'There was a problem communicating with "%1": %2',
                            __($label),
                            $e->getMessage()
                        )
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical('Error occurred while testing MasterCard configuration: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }
}
