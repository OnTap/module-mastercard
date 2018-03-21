<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Block\Checkout\Review;

use Magento\Checkout\Block\Onepage\Link;

class PlaceOrder extends Link
{
    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->_checkoutSession->getQuote()->getPayment()->getMethod();
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->_checkoutSession->getQuote()->getCustomerEmail();
    }
}
