<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request\Visa;

use Magento\Payment\Gateway\Request\BuilderInterface;
use OnTap\MasterCard\Api\WalletPaymentInterface;

class SessionFromWallet implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [
            'wallet' => [
                'visaCheckout' => [
                    WalletPaymentInterface::CALL_ID => $buildSubject[WalletPaymentInterface::CALL_ID]
                ]
            ]
        ];
    }
}
