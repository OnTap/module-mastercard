<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Http\ThreeDSecure;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use OnTap\Tns\Gateway\Http\TransferFactory;

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
