<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Api\Data;

interface WalletDataInterface
{
    const WALLET_PROVIDER = 'walletProvider';
    const ALLOWED_CARD_TYPES = 'allowedCardTypes';
    const MERCHANT_CHECKOUT_ID = 'merchantCheckoutId';
    const ORIGIN_URL = 'originUrl';
    const REQUEST_TOKEN = 'requestToken';
    const ENCRYPTED_DATA = 'encryptedData';

    /**
     * @param string $encryptedData
     * @return $this
     */
    public function setEncryptedData($encryptedData);

    /**
     * @return string
     */
    public function getEncryptedData();

    /**
     * @param string $allowedCardTypes
     * @return $this
     */
    public function setAllowedCardTypes($allowedCardTypes);

    /**
     * @param string $merchantCheckoutId
     * @return $this
     */
    public function setMerchantCheckoutId($merchantCheckoutId);

    /**
     * @param string $originUrl
     * @return $this
     */
    public function setOriginUrl($originUrl);

    /**
     * @param string $requestToken
     * @return $this
     */
    public function setRequestToken($requestToken);

    /**
     * @return string
     */
    public function getAllowedCardTypes();

    /**
     * @return string
     */
    public function getMerchantCheckoutId();

    /**
     * @return string
     */
    public function getOriginUrl();

    /**
     * @return string
     */
    public function getRequestToken();

    /**
     * @param string $walletProvider
     * @return $this
     */
    public function setWalletProvider($walletProvider);

    /**
     * @return string
     */
    public function getWalletProvider();
}
