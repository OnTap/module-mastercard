<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\BillingAddressManagementInterface;
use OnTap\MasterCard\Api\SessionManagementInterface;

class SessionInformationManagement implements SessionManagementInterface
{
    const CREATE_SESSION = 'create_session';
    const OPEN_WALLET = 'open_wallet';

    /**
     * @var CommandPoolInterface
     */
    protected $commandPool;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var PaymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var BillingAddressManagementInterface
     */
    protected $billingAddressManagement;

    /**
     * SessionInformationManagement constructor.
     * @param CommandPoolInterface $commandPool
     * @param CartRepositoryInterface $quoteRepository
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param BillingAddressManagementInterface $billingAddressManagement
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        CartRepositoryInterface $quoteRepository,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        BillingAddressManagementInterface $billingAddressManagement
    ) {
        $this->commandPool = $commandPool;
        $this->quoteRepository = $quoteRepository;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->billingAddressManagement = $billingAddressManagement;
    }

    /**
     * {@inheritDoc}
     */
    public function createNewPaymentSession(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($billingAddress) {
            $this->billingAddressManagement->assign($cartId, $billingAddress);
        }

        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $quote->setReservedOrderId(null);
        $quote->reserveOrderId();

        $this->commandPool
            ->get(self::CREATE_SESSION)
            ->execute([
                'payment' => $this->paymentDataObjectFactory->create($quote->getPayment())
            ]);

        $session = $quote->getPayment()->getAdditionalInformation('session');

        $quote->save();

        return [
            'id' => (string) $session['id'],
            'version' => (string) $session['version']
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function createNewGuestPaymentSession(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory
            ->create()
            ->load($cartId, 'masked_id');

        $billingAddress->setEmail($email);
        return $this->createNewPaymentSession($quoteIdMask->getQuoteId(), $paymentMethod, $billingAddress);
    }

    /**
     * @inheritdoc
     */
    public function openWallet(
        $cartId,
        $sessionId,
        $type
    ) {
        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $quote->getPayment()->setAdditionalInformation('wallet', [
            'type' => $type
        ]);

        $this->commandPool
            ->get(self::OPEN_WALLET)
            ->execute([
                'payment' => $this->paymentDataObjectFactory->create($quote->getPayment())
            ]);

        $wallet = $quote->getPayment()->getAdditionalInformation('wallet');
        $quote->save();

        return [

        ];
    }

    /**
     * @inheritdoc
     */
    public function openWalletGuest(
        $cartId,
        $email,
        $sessionId,
        $type
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory
            ->create()
            ->load($cartId, 'masked_id');

        return $this->openWallet($quoteIdMask->getQuoteId(), $sessionId, $type);
    }
}
