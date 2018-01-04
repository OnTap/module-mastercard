<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Api;

interface SessionManagementInterface
{
    /**
     * Create a new payment session for the customer
     *
     * @param string $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return string[]
     */
    public function createNewPaymentSession(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    );

    /**
     * Create a new payment session for the guest user
     *
     * @param string $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return string[]
     */
    public function createNewGuestPaymentSession(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    );

    /**
     * Open the wallet for customer
     *
     * @param string $cartId
     * @param string $sessionId
     * @param string $type
     * @return \OnTap\MasterCard\Api\Data\WalletDataInterface
     */
    public function openWallet(
        $cartId,
        $sessionId,
        $type
    );

    /**
     * Open the wallet for a guest user
     *
     * @param string $cartId
     * @param string $email
     * @param string $sessionId
     * @param string $type
     * @return \OnTap\MasterCard\Api\Data\WalletDataInterface
     */
    public function openWalletGuest(
        $cartId,
        $email,
        $sessionId,
        $type
    );
}
