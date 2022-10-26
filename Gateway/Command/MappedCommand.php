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
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Store\Model\StoreManagerInterface;
use OnTap\MasterCard\Gateway\Config\ConfigInterface;

class MappedCommand implements CommandInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var ConfigInterface
     */
    private $configProvider;

    /**
     * @var string
     */
    private $configKey;

    /**
     * @var array
     */
    private $commandMap;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CommandPoolInterface $commandPool
     * @param ConfigInterface $configProvider
     * @param string $configKey
     * @param array $commandMap
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CommandPoolInterface $commandPool,
        ConfigInterface $configProvider,
        $configKey = null,
        $commandMap = []
    ) {
        $this->storeManager = $storeManager;
        $this->commandPool = $commandPool;
        $this->configProvider = $configProvider;
        $this->configKey = $configKey;
        $this->commandMap = $commandMap;
    }

    /**
     * @param array $commandSubject
     *
     * @return ResultInterface|null
     * @throws NotFoundException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function execute(array $commandSubject)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $configValue = $this->configProvider->getValue($this->configKey, $storeId);

        $commandNames = $this->commandMap[$configValue] ?? null;
        if (!$commandNames) {
            throw new InvalidArgumentException('commandMap has to be provided');
        }

        if (!is_array($commandNames)) {
            $commandNames = [$commandNames];
        }

        foreach ($commandNames as $commandName) {
            $this->commandPool
                ->get($commandName)
                ->execute($commandSubject);
        }

        return null;
    }
}
