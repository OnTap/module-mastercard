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

namespace OnTap\MasterCard\Model;

use Magento\Store\Model\StoreManagerInterface;
use OnTap\MasterCard\Api\VerifyPaymentFlagInterface;
use OnTap\MasterCard\Gateway\Config\Config;

class VerifyPaymentFlag implements VerifyPaymentFlagInterface
{
    private const CONFIG_PATH_ACTIVE = 'active';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $configPool;

    /**
     * @param StoreManagerInterface $storeManager
     * @param array $configPool
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        array $configPool = []
    ) {
        $this->storeManager = $storeManager;
        $this->configPool = $configPool;
    }

    /**
     * {@inheritDoc}
     */
    public function isVerifyPayment(string $method): bool
    {
        /** @var Config|null $configProvider */
        $configProvider = $this->configPool[$method]['configProvider'] ?? null;
        $configPath = $this->configPool[$method]['configPath'] ?? null;
        $verifyOperationValue = $this->configPool[$method]['configValue'] ?? null;
        if (in_array(null, [$configProvider, $configPath, $verifyOperationValue], true)) {
            return false;
        }

        $storeId = $this->storeManager->getStore()->getId();
        $isActive = (bool)$configProvider->getValue(self::CONFIG_PATH_ACTIVE, $storeId);

        $paymentOperation = $configProvider->getValue($configPath, $storeId);
        $isVerifyOperation = $paymentOperation === $verifyOperationValue;

        return $isActive && $isVerifyOperation;
    }
}
