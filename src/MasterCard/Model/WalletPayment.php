<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Model;

use OnTap\MasterCard\Api\Data\SessionDataInterface;
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
     * WalletPayment constructor.
     * @param \OnTap\MasterCard\Api\Data\SessionDataInterfaceFactory $sessionDataFactory
     * @param CheckoutSession $checkoutSession
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     */
    public function __construct(
        \OnTap\MasterCard\Api\Data\SessionDataInterfaceFactory $sessionDataFactory,
        CheckoutSession $checkoutSession,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->sessionDataFactory = $sessionDataFactory;
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

        $sessionData = $paymentDO->getPayment()->getAdditionalInformation('session');

        $session = $this->sessionDataFactory->create(['data' => [
            SessionDataInterface::SESSION_ID => $sessionData['id'],
            SessionDataInterface::SESSION_VERSION => $sessionData['version']
        ]]);

        return $session;
    }
}
