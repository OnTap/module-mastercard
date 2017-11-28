<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Gateway\Request\Masterpass;

use Magento\Payment\Gateway\Request\BuilderInterface;

class OAuth implements BuilderInterface
{
    const OAUTH_TOKEN = 'oauthToken';
    const OAUTH_VERIFIER = 'oauthVerifier';
    const CHECKOUT_URL = 'checkoutUrl';

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
                    self::OAUTH_TOKEN => $buildSubject[self::OAUTH_TOKEN],
                    self::OAUTH_VERIFIER => $buildSubject[self::OAUTH_VERIFIER],
                    self::CHECKOUT_URL => $buildSubject[self::CHECKOUT_URL]
                ]
            ]
        ];
    }
}
