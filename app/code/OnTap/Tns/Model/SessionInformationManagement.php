<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Model;

use OnTap\Tns\Api\SessionInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Command\CommandPoolInterface;

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
     * SessionInformationManagement constructor.
     * @param CommandPoolInterface $commandPool
     * @param CartRepositoryInterface $quoteRepository
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        CartRepositoryInterface $quoteRepository,
        PaymentDataObjectFactory $paymentDataObjectFactory
    ) {
        $this->commandPool = $commandPool;
        $this->quoteRepository = $quoteRepository;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function createNewPaymentSession(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
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
}
