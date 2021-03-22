<?php
/**
 * Copyright (c) 2016-2019 Mastercard
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

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use OnTap\MasterCard\Gateway\Config\ConfigFactory;

class OrderDataBuilder implements BuilderInterface
{
    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * OrderDataBuilder constructor.
     * @param ConfigFactory $configFactory
     */
    public function __construct(ConfigFactory $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        $storeId = $order->getStoreId();

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        $config = $this->configFactory->create();
        $config->setMethodCode($payment->getMethod());

        $total = $order->getGrandTotalAmount();

        $orderId = $paymentDO->getOrder()->getOrderIncrementId();

        return [
            'order' => [
                'amount' => sprintf('%.2F', $total),
                'currency' => $order->getCurrencyCode(),
                'notificationUrl' => $config->getWebhookNotificationUrl($storeId),
                'reference' => $orderId,
            ]
        ];
    }
}
