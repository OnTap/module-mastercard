<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request\Masterpass;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class OpenWallet implements BuilderInterface
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
                'masterpass' => [
                    'originUrl' => 'http://mastercard-m2.dev/2.2/checkout/#payment'
                ]
            ]
        ];
    }
}
