<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Http\Hosted;

use OnTap\MasterCard\Gateway\Http\TransferFactory;
use OnTap\MasterCard\Gateway\Http\Client\Rest;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

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
        return $this->getGatewayUri() . 'order/'.$orderId;
    }
}
