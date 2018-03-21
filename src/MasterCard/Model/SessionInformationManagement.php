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
use OnTap\MasterCard\Gateway\Config\Wallet\CommandProvider;
use OnTap\MasterCard\Api\PaymentMethodManagementInterface;

class SessionInformationManagement implements SessionManagementInterface
{
    const CREATE_SESSION = 'create_session';

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
     * @var CommandProvider
     */
    protected $commandProvider;

    /**
     * @var WalletFactory
     */
    protected $walletFactory;

    /**
     * @var PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * SessionInformationManagement constructor.
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CommandPoolInterface $commandPool
     * @param CartRepositoryInterface $quoteRepository
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param BillingAddressManagementInterface $billingAddressManagement
     * @param CommandProvider $commandProvider
     * @param WalletFactory $walletFactory
     */
    public function __construct(
        PaymentMethodManagementInterface $paymentMethodManagement,
        CommandPoolInterface $commandPool,
        CartRepositoryInterface $quoteRepository,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        BillingAddressManagementInterface $billingAddressManagement,
        CommandProvider $commandProvider,
        WalletFactory $walletFactory
    ) {
        $this->commandPool = $commandPool;
        $this->quoteRepository = $quoteRepository;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->billingAddressManagement = $billingAddressManagement;
        $this->commandProvider = $commandProvider;
        $this->walletFactory = $walletFactory;
        $this->paymentMethodManagement = $paymentMethodManagement;
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

//        $quote->setReservedOrderId(null);
//        $quote->reserveOrderId();

        $this->paymentMethodManagement->set($cartId, $paymentMethod);

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
        $email = null,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory
            ->create()
            ->load($cartId, 'masked_id');

        if ($billingAddress && $email) {
            $billingAddress->setEmail($email);
        }
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

        $quote->getPayment()->setAdditionalInformation('walletProvider', $type);

        $paymentDO =  $this->paymentDataObjectFactory->create($quote->getPayment());

        $command = $this->commandProvider->getByType($type);
        $this->commandPool
            ->get($command)
            ->execute([
                'payment' => $paymentDO
            ]);

        $walletData = $quote->getPayment()->getAdditionalInformation('wallet');
        $walletProvider = $quote->getPayment()->getAdditionalInformation('walletProvider');

        $quote->getPayment()->save();
        $quote->save();

        $wallet = $this->walletFactory->create(['data' => [
            'walletProvider' => $walletProvider
        ]]);

        $type = key($walletData);
        $wallet->addData($walletData[$type]);

        return $wallet;
    }

    /**
     * @inheritdoc
     */
    public function openWalletGuest(
        $cartId,
        $email = null,
        $sessionId,
        $type
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory
            ->create()
            ->load($cartId, 'masked_id');

        return $this->openWallet($quoteIdMask->getQuoteId(), $sessionId, $type);
    }
}
