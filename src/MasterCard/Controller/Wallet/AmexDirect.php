<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Controller\Wallet;

use OnTap\MasterCard\Gateway\Request\Amex\SessionFromWallet;
use OnTap\MasterCard\Controller\Wallet;
use Magento\Framework\App\Action\Context;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;

class AmexDirect extends Wallet
{
    const UPDATE_WALLET_COMMAND = 'update_amex_wallet';

    /**
     * @var CommandPoolInterface
     */
    protected $commandPool;

    /**
     * AmexDirect constructor.
     * @param CommandPoolInterface $commandPool
     * @param Context $context
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param PaymentInformationManagementInterface $paymentInformationManagement
     * @param GuestPaymentInformationManagementInterface $guestPaymentInformationManagement
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        Context $context,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        PaymentInformationManagementInterface $paymentInformationManagement,
        GuestPaymentInformationManagementInterface $guestPaymentInformationManagement
    ) {
        parent::__construct(
            $context,
            $paymentDataObjectFactory,
            $checkoutSession,
            $customerSession,
            $paymentInformationManagement,
            $guestPaymentInformationManagement
        );
        $this->commandPool = $commandPool;
    }

    /**
     * @inheritdoc
     */
    protected function getMethod()
    {
        return 'tns_direct_amex';
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        $payment = $quote->getPayment();
        $paymentDO = $this->paymentDataObjectFactory->create($payment);

        try {
            $this->commandPool
                ->get(self::UPDATE_WALLET_COMMAND)
                ->execute([
                    'payment' => $paymentDO,
                    SessionFromWallet::AUTH_CODE => $this->getRequest()->getParam(SessionFromWallet::AUTH_CODE),
                    SessionFromWallet::TRANS_ID => $this->getRequest()->getParam(SessionFromWallet::TRANS_ID),
                    SessionFromWallet::WALLET_ID => $this->getRequest()->getParam(SessionFromWallet::WALLET_ID),
                    SessionFromWallet::SEL_CARD_TYPE => $this->getRequest()->getParam(SessionFromWallet::SEL_CARD_TYPE),
                ]);

            $payment->setMethod($this->getMethod());
            $quote->getPayment()->save();

            if ($this->customerSession->isLoggedIn()) {
                $this->paymentInformationManagement->savePaymentInformationAndPlaceOrder(
                    $quote->getId(),
                    $payment
                );
            } else {
                $this->guestPaymentInformationManagement->savePaymentInformationAndPlaceOrder(
                    $this->getRequest()->getParam(Wallet::QUOTE_ID),
                    $this->getRequest()->getParam(Wallet::GUEST_EMAIL),
                    $payment
                );
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('checkout/cart');
        }

        return $this->_redirect('checkout/onepage/success');
    }
}
