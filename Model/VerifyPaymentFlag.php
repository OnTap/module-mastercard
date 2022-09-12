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
use OnTap\MasterCard\Api\MethodInterface;
use OnTap\MasterCard\Api\VerifyPaymentFlagInterface;
use OnTap\MasterCard\Gateway\Config\Config;

class VerifyPaymentFlag implements VerifyPaymentFlagInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config[]
     */
    private $configPool;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Config[] $configPool
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
        $config = $this->configPool[$method] ?? null;
        if (null === $config) {
            return false;
        }

        $storeId = $this->storeManager->getStore()->getId();
        $isActive = (bool)$config->getValue('active', $storeId);
        $paymentOperation = $config->getValue('mapped_payment_action', $storeId);
        $isVerifyOperation = $paymentOperation === MethodInterface::MAPPED_ACTION_ORDER_VERIFY;

        return $isActive && $isVerifyOperation;
    }
}
