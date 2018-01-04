<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request\Amex;

use Magento\Payment\Gateway\Request\BuilderInterface;

class SessionFromWallet implements BuilderInterface
{
    const AUTH_CODE = 'authCode';
    const SEL_CARD_TYPE = 'selectedCardType';
    const TRANS_ID = 'transactionId';
    const WALLET_ID = 'walletId';
    const GUEST_EMAIL = 'guestEmail';
    const QUOTE_ID = 'quoteId';

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
                'amexExpressCheckout' => [
                    self::AUTH_CODE => $buildSubject[self::AUTH_CODE],
                    self::SEL_CARD_TYPE => $buildSubject[self::SEL_CARD_TYPE],
                    self::TRANS_ID => $buildSubject[self::TRANS_ID],
                    self::WALLET_ID => $buildSubject[self::WALLET_ID],
                ]
            ]
        ];
    }
}
