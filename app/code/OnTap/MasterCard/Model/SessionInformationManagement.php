<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Model;

use OnTap\MasterCard\Api\SessionInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\BillingAddressManagementInterface;

class SessionInformationManagement implements SessionInformationManagementInterface
{
    const CREATE_HOSTED_SESSION = 'create_session';

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
            ->get(static::CREATE_HOSTED_SESSION)
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

        return $this->createNewPaymentSession($quoteIdMask->getQuoteId(), $paymentMethod, $billingAddress);
    }
}
