<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Model;

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
     * WalletPayment constructor.
     * @param CheckoutSession $checkoutSession
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function updateSessionFromWallet(
        $authCode,
        $selCardType,
        $transId,
        $walletId
    ) {
        $quote = $this->checkoutSession->getQuote();

        $paymentDO = $this->paymentDataObjectFactory->create($quote->getPayment());
        $this->commandPool
            ->get(WalletPaymentInterface::UPDATE_WALLET_COMMAND)
            ->execute([
                'payment' => $paymentDO,
                WalletPaymentInterface::AUTH_CODE => $authCode,
                WalletPaymentInterface::TRANS_ID => $transId,
                WalletPaymentInterface::WALLET_ID => $walletId,
                WalletPaymentInterface::SEL_CARD_TYPE => $selCardType,
            ]);

        $quote->getPayment()->save();

        return true;
    }
}
