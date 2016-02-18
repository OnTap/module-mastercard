<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Request\Hosted;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Data\Quote\QuoteAdapter;
use Magento\Checkout\Model\CartFactory;
use Magento\Checkout\Model\Cart;

class OrderDataBuilder implements BuilderInterface
{
    /**
     * @var Cart
     */
    private $cart;

    /**
     * OrderDataBuilder constructor.
     * @param CartFactory $cartFactory
     */
    public function __construct(CartFactory $cartFactory)
    {
        $this->cart = $cartFactory->create();
    }

    /**
     * @return array
     */
    protected function getItemData()
    {
        $data = [];
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($this->cart->getItems() as $item) {
            $data[] = [
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'sku' => $item->getSku(),
                'unitPrice' => $item->getBasePrice(),
                'quantity' => (int) $item->getQty(),
                'unitTaxAmount' => $item->getBaseTaxAmount() / (float) $item->getQty(),
            ];
        }

        return $data;
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

        /* @var QuoteAdapter $order */
        $order = $paymentDO->getOrder();

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $payment->getQuote();
        $quote->collectTotals();

        return [
            'order' => [
                'amount' => sprintf('%.2F', $quote->getGrandTotal()),
                'currency' => $order->getCurrencyCode(),
                'id' => $order->getOrderIncrementId(),
                //'item' => $this->getItemData(),
                //'shippingAndHandlingAmount' => $quote->getShippingAmount(),
            ]
        ];
    }
}
