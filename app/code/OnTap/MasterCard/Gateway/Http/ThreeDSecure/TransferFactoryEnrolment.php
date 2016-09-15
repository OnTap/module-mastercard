<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Http\ThreeDSecure;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use OnTap\MasterCard\Gateway\Http\TransferFactory;

class TransferFactoryEnrolment extends TransferFactory
{
    /**
     * @param PaymentDataObjectInterface $payment
     * @return string
     */
    protected function getUri(PaymentDataObjectInterface $payment)
    {
        $threeDSecureId = uniqid(sprintf('3DS-'));
        return $this->getGatewayUri() . '3DSecureId/' . $threeDSecureId;
    }
}
