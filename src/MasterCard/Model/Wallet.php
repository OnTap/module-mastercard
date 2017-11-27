<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Model;

use Magento\Framework\Model\AbstractModel;
use OnTap\MasterCard\Api\Data\WalletDataInterface;

class Wallet extends AbstractModel implements WalletDataInterface
{
    /**
     * @inheritdoc
     */
    public function setWalletProvider($walletProvider)
    {
        $this->setData(WalletDataInterface::WALLET_PROVIDER, $walletProvider);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWalletProvider()
    {
        return $this->getData(WalletDataInterface::WALLET_PROVIDER);
    }

    /**
     * @inheritdoc
     */
    public function setEncryptedData($encryptedData)
    {
        $this->setData($encryptedData, WalletDataInterface::ENCRYPTED_DATA);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEncryptedData()
    {
        return $this->getData(WalletDataInterface::ENCRYPTED_DATA);
    }

    /**
     * @inheritdoc
     */
    public function setAllowedCardTypes($allowedCardTypes)
    {
        $this->setData(WalletDataInterface::ALLOWED_CARD_TYPES);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setMerchantCheckoutId($merchantCheckoutId)
    {
        $this->setData(WalletDataInterface::MERCHANT_CHECKOUT_ID);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOriginUrl($originUrl)
    {
        $this->setData(WalletDataInterface::ORIGIN_URL);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setRequestToken($requestToken)
    {
        $this->setData(WalletDataInterface::REQUEST_TOKEN);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedCardTypes()
    {
        return $this->getData(WalletDataInterface::ALLOWED_CARD_TYPES);
    }

    /**
     * @inheritdoc
     */
    public function getMerchantCheckoutId()
    {
        return $this->getData(WalletDataInterface::MERCHANT_CHECKOUT_ID);
    }

    /**
     * @inheritdoc
     */
    public function getOriginUrl()
    {
        return $this->getData(WalletDataInterface::ORIGIN_URL);
    }

    /**
     * @inheritdoc
     */
    public function getRequestToken()
    {
        return $this->getData(WalletDataInterface::REQUEST_TOKEN);
    }
}
