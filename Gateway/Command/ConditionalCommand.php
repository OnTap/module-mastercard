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

namespace OnTap\MasterCard\Gateway\Command;

use InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Store\Model\StoreManagerInterface;
use OnTap\MasterCard\Gateway\Config\ConfigInterface;

class ConditionalCommand implements CommandInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigInterface
     */
    private $configProvider;

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var string|null
     */
    private $configKey;

    /**
     * @var string|null
     */
    private $commandName;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $configProvider
     * @param CommandPoolInterface $commandPool
     * @param string $configKey
     * @param string $commandName
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigInterface $configProvider,
        CommandPoolInterface $commandPool,
        $configKey = null,
        $commandName = null
    ) {
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
        $this->commandPool = $commandPool;
        $this->configKey = $configKey;
        $this->commandName = $commandName;
    }

    /**
     * @param array $commandSubject
     *
     * @return ResultInterface|null
     * @throws NoSuchEntityException
     * @throws NotFoundException
     * @throws CommandException
     */
    public function execute(array $commandSubject)
    {
        if (!$this->commandName || !$this->configKey) {
            throw new InvalidArgumentException('configKey and commandName have to be provided');
        }

        $storeId = $this->storeManager->getStore()->getId();
        $configValue = $this->configProvider->getValue($this->configKey, $storeId);

        if (!$configValue) {
            return null;
        }

        return $this->commandPool
            ->get($this->commandName)
            ->execute($commandSubject);
    }
}
