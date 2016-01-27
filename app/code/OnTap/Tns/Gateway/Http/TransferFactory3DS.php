<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Http;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class TransferFactory3DS extends TransferFactory
{
    /**
     * @param PaymentDataObjectInterface $payment
     * @return string
     */
    protected function getUri(PaymentDataObjectInterface $payment)
    {
        $threeDSecureId = uniqid(sprintf('3DS-%s-', (string) $payment->getOrder()->getOrderIncrementId()));
        return $this->getGatewayUri() . '3DSecureId/' . $threeDSecureId;
    }
}
