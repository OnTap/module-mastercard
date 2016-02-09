<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Http\ThreeDSecure;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use OnTap\Tns\Gateway\Http\Client\Rest;
use OnTap\Tns\Gateway\Http\TransferFactory;

class TransferFactoryProcess extends TransferFactory
{
    /**
     * @var string
     */
    protected $httpMethod = Rest::POST;

    /**
     * @param PaymentDataObjectInterface $payment
     * @return string
     */
    protected function getUri(PaymentDataObjectInterface $payment)
    {
        $threeDSId = $payment->getPayment()->getAdditionalInformation('3DSecureId');
        if (!$threeDSId) {
            throw new \InvalidArgumentException("3D-Secure ID not provided");
        }
        return $this->getGatewayUri() . '3DSecureId/' . $threeDSId;
    }
}
