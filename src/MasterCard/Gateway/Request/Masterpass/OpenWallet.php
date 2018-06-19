<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request\Masterpass;

use Magento\Payment\Gateway\Request\BuilderInterface;

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
            // @todo: generate url
            'wallet' => [
                'masterpass' => [
                    'originUrl' => 'https://mastercard-m2.local/'
                ]
            ]
        ];
    }
}
