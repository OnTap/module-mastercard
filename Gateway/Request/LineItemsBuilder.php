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

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item;
use OnTap\MasterCard\Gateway\Config\ConfigFactory;

class LineItemsBuilder implements BuilderInterface
{
    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * LineItemsBuilder constructor.
     * @param ConfigFactory $configFactory
     */
    public function __construct(ConfigFactory $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    /**
     * @param OrderItemInterface[]|null $items
     * @return array
     */
    protected function getOrderItems($items)
    {
        $result = [];

        /** @var Item $item */
        foreach ($items as $item) {
            if ($item->getParentItemId() !== null) {
                continue;
            }
            $unitPrice = $item->getBaseRowTotal() - $item->getBaseTotalDiscountAmount();
            $result[] = [
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'sku' => $item->getSku(),
                'unitPrice' => sprintf('%.2F', $unitPrice + $item->getBaseDiscountTaxCompensationAmount()),
                'quantity' => 1,
                //'unitTaxAmount' => $item->getBaseTaxAmount(),
            ];
        }
        return $result;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();

        $config = $this->configFactory->create();
        $config->setMethodCode($payment->getMethodInstance()->getCode());

        if ($config->isSendLineItems($order->getStoreId())) {
            $shippingAmount = $payment->getBaseShippingAmount();
            $taxAmount = $payment->getOrder()->getBaseTaxAmount();

            return [
                'order' => [
                    'item' => $this->getOrderItems($order->getItems()),
                    'shippingAndHandlingAmount' => $shippingAmount,
                    'taxAmount' => $taxAmount,
                ]
            ];
        }

        return [];
    }
}
