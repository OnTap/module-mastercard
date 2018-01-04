<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Controller\Wallet;

use OnTap\MasterCard\Gateway\Request\Amex\SessionFromWallet;
use OnTap\MasterCard\Controller\Wallet;

class Amex extends Wallet
{
    const UPDATE_WALLET_COMMAND = 'update_amex_wallet';

    /**
     * @return string
     */
    protected function getMethod()
    {
        return 'tns_direct_amex';
    }

    /**
     * @throws \Exception
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
                    $this->getRequest()->getParam(SessionFromWallet::QUOTE_ID),
                    $this->getRequest()->getParam(SessionFromWallet::GUEST_EMAIL),
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
