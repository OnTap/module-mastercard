<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Block\Checkout\Review;

class Shipping extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @todo Create a before interceptor for this
     * @return string
     */
    public function getJsLayout()
    {
        $config = [];
        if (isset($this->jsLayout['components']['review-shipping-address']['config'])) {
            $config = $this->jsLayout['components']['review-shipping-address']['config'];
        }
        $this->jsLayout['components']['review-shipping-address']['config'] = array_merge($config, [
            'shippingFromWallet' => $this->getWalletShippingAddress()
        ]);
        return parent::getJsLayout();
    }

    /**
     * @return array
     */
    protected function getWalletShippingAddress()
    {
        return $this->_checkoutSession->getQuote()->getShippingAddress()->exportCustomerAddress()->__toArray();
    }
}
