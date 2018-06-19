<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Model;

use OnTap\MasterCard\Api\Data\SessionDataInterface;
use OnTap\MasterCard\Api\PaymentMethodManagementInterface;
use OnTap\MasterCard\Api\WalletPaymentInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;

class WalletPayment implements WalletPaymentInterface
{
    /**
     * @var CommandPoolInterface
     */
    protected $commandPool;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var PaymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;

    /**
     * @var \OnTap\MasterCard\Api\Data\SessionDataInterfaceFactory
     */
    protected $sessionDataFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\Quote\Api\GuestBillingAddressManagementInterface
     */
    protected $billingAddressManagement;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * WalletPayment constructor.
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \OnTap\MasterCard\Api\Data\SessionDataInterfaceFactory $sessionDataFactory
     * @param CheckoutSession $checkoutSession
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     */
    public function __construct(
        \OnTap\MasterCard\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \OnTap\MasterCard\Api\Data\SessionDataInterfaceFactory $sessionDataFactory,
        CheckoutSession $checkoutSession,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory
    ) {
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->billingAddressManagement = $billingAddressManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->checkoutSession = $checkoutSession;
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->sessionDataFactory = $sessionDataFactory;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function updateSessionFromVisaWallet(
        $callId
    ) {
        $quote = $this->checkoutSession->getQuote();
        $paymentDO = $this->paymentDataObjectFactory->create($quote->getPayment());

        $paymentDO
            ->getPayment()
            ->setAdditionalInformation('walletProvider', 'VISA_CHECKOUT');

        $this->commandPool
            ->get(WalletPaymentInterface::UPDATE_VISA_WALLET_COMMAND)
            ->execute([
                'payment' => $paymentDO,
                WalletPaymentInterface::CALL_ID => $callId
            ]);

        $quote->getPayment()->save();

        $sessionData = $paymentDO->getPayment()->getAdditionalInformation('session');
        $session = $this->sessionDataFactory->create(['data' => [
            SessionDataInterface::SESSION_ID => $sessionData['id'],
            SessionDataInterface::SESSION_VERSION => $sessionData['version']
        ]]);

        return $session;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function updateSessionFromAmexWallet(
        $authCode,
        $selCardType,
        $transId,
        $walletId
    ) {
        $quote = $this->checkoutSession->getQuote();

        $paymentDO = $this->paymentDataObjectFactory->create($quote->getPayment());
        $this->commandPool
            ->get(WalletPaymentInterface::UPDATE_AMEX_WALLET_COMMAND)
            ->execute([
                'payment' => $paymentDO,
                WalletPaymentInterface::AUTH_CODE => $authCode,
                WalletPaymentInterface::TRANS_ID => $transId,
                WalletPaymentInterface::WALLET_ID => $walletId,
                WalletPaymentInterface::SEL_CARD_TYPE => $selCardType,
            ]);

        $quote->getPayment()->save();

        $sessionData = $paymentDO->getPayment()->getAdditionalInformation('session');

        $session = $this->sessionDataFactory->create(['data' => [
            SessionDataInterface::SESSION_ID => $sessionData['id'],
            SessionDataInterface::SESSION_VERSION => $sessionData['version']
        ]]);

        return $session;
    }

    /**
     * @inheritdoc
     */
    public function saveGuestPaymentInformation(
        $cartId,
        $email = null,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        if ($billingAddress) {
            $this->billingAddressManagement->assign($cartId, $billingAddress);
        } else {
            $billingAddress = $this->cartRepository->getActive($quoteIdMask->getQuoteId())->getBillingAddress();
        }
        if ($email) {
            $billingAddress->setEmail($email);
        }

        $this->paymentMethodManagement->set($quoteIdMask->getQuoteId(), $paymentMethod);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function savePaymentInformation(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($billingAddress) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->cartRepository->getActive($cartId);
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->setBillingAddress($billingAddress);
            $quote->setDataChanges(true);
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress && $shippingAddress->getShippingMethod()) {
                $shippingDataArray = explode('_', $shippingAddress->getShippingMethod());
                $shippingCarrier = array_shift($shippingDataArray);
                $shippingAddress->setLimitCarrier($shippingCarrier);
            }
        }

        $this->paymentMethodManagement->set($cartId, $paymentMethod);

        return true;
    }
}
