<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Http\Hosted;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use OnTap\MasterCard\Gateway\Http\Client\Rest;
use OnTap\MasterCard\Gateway\Http\TransferFactory;

class TransferFactoryOrder extends TransferFactory
{
    /**
     * @var string
     */
    protected $httpMethod = Rest::GET;

    /**
     * @param PaymentDataObjectInterface $payment
     * @return string
     */
    protected function getUri(PaymentDataObjectInterface $payment)
    {
        $orderId = $payment->getOrder()->getOrderIncrementId();
        $storeId = $payment->getOrder()->getStoreId();
        return $this->getGatewayUri($storeId) . 'order/' . $orderId;
    }
}
