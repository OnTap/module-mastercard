<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use OnTap\MasterCard\Gateway\Config\ConfigFactory;
use OnTap\MasterCard\Model\Method\WalletInterface;

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
                'unitPrice' => sprintf('%.2F', $item->getRowTotal() - $item->getDiscountAmount()),
                'quantity' => 1,
                //'unitTaxAmount' => $item->getBaseTaxAmount(),
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        // This method is also used by Wallets as well, so $config can not be directly fabricated,
        // because method code refers to wallet method, tho wallets use config from providers instead
        // @todo: Unify config usage somehow by extending the base Adapter (in use by non-Wallet methods)
        $method = $payment->getMethodInstance();
        if ($method instanceof WalletInterface) {
            $config = $method->getProviderConfig();
        } else {
            $config = $this->configFactory->create();
            $config->setMethodCode($payment->getMethod());
        }

        return [
            'order' => [
                'item' => $this->getOrderItems($order->getItems()),
                'shippingAndHandlingAmount' => $payment->getShippingAmount(),
                //'discount' => $this->getDiscountData($payment),
                'taxAmount' => $payment->getOrder()->getTaxAmount(),
                'notificationUrl' => $config->getWebhookNotificationUrl(),
            ]
        ];
    }
}
