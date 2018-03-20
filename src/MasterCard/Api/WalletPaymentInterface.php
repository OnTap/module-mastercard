<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Api;

interface WalletPaymentInterface
{
    const UPDATE_WALLET_COMMAND = 'update_amex_wallet';

    const AUTH_CODE = 'authCode';
    const SEL_CARD_TYPE = 'selectedCardType';
    const TRANS_ID = 'transactionId';
    const WALLET_ID = 'walletId';

    /**
     * Update current session with data from wallet
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @param string $authCode
     * @param string $selCardType
     * @param string $transId
     * @param string $walletId
     * @return \OnTap\MasterCard\Api\Data\SessionDataInterface
     */
    public function updateSessionFromWallet(
        $authCode,
        $selCardType,
        $transId,
        $walletId
    );
}
