<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Controller\Wallet;

use OnTap\MasterCard\Controller\Wallet;

class Amex extends Wallet
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        $payment = $quote->getPayment();

        try {
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
