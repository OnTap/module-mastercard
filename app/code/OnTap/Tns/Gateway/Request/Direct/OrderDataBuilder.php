<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Request\Direct;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;

class OrderDataBuilder implements BuilderInterface
{
    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface[]|null $items
     * @return array
     */
    protected function getOrderItems($items)
    {
        $result = [];

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($items as $item) {
            if ($item->isDummy(true)) {
                continue;
            }
            $result[] = [
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'sku' => $item->getSku(),
                'unitPrice' => $item->getBasePrice(),
                'quantity' => (int) $item->getQtyOrdered(),
                'unitTaxAmount' => $item->getBaseTaxAmount(),
            ];
        }
        return $result;
    }

    /**
     * @param Payment $payment
     * @return array
     */
    protected function getDiscountData(Payment $payment)
    {
        $discount = abs($payment->getOrder()->getBaseDiscountAmount());
        return [
            'amount' => $discount
        ];
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

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        return [
            'order' => [
                'amount' => sprintf('%.2F', SubjectReader::readAmount($buildSubject)),
                'currency' => $order->getCurrencyCode(),
                'item' => $this->getOrderItems($order->getItems()),
                'shippingAndHandlingAmount' => $payment->getShippingAmount(),
                'discount' => $this->getDiscountData($payment)
            ]
        ];
    }
}
