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

namespace OnTap\MasterCard\Plugin\Magento\Sales\Api\OrderRepository;

use Magento\Framework\AuthorizationInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class AfterGetPlugin
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @param AuthorizationInterface $authorization
     * @param OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        AuthorizationInterface $authorization,
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->authorization = $authorization;
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $result
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, $result)
    {
        if (!$this->authorization->isAllowed('OnTap_MasterCard::sales_order_view_token')) {
            return $result;
        }

        $additionalInfo = $result->getPayment()->getAdditionalInformation();
        $paymentToken = $additionalInfo['token'] ?? null;
        if (!$paymentToken) {
            return $result;
        }

        $extensionAttributes = $result->getExtensionAttributes();
        if (!$extensionAttributes){
            $extensionAttributes = $this->orderExtensionFactory->create();
        }
        $extensionAttributes->setToken($paymentToken);
        $result->setExtensionAttributes($extensionAttributes);

        return $result;
    }
}
