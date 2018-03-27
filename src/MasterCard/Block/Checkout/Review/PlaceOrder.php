<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Block\Checkout\Review;

use Magento\Checkout\Block\Onepage\Link;

class PlaceOrder extends Link
{
    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $method = $this->_checkoutSession->getQuote()->getPayment()->getMethodInstance();

        $config = $this->jsLayout['components']['mpgs-checkout-review-place-order']['config'];
        $config = array_merge($config, [
            'email' => $this->_checkoutSession->getQuote()->getCustomerEmail(),
            'method' => $this->_checkoutSession->getQuote()->getPayment()->getMethod(),
            'check_url' => $this->getUrl(
                'tns/threedsecure/check',
                [
                    'method' => $this->_checkoutSession->getQuote()->getPayment()->getMethod(),
                    '_secure' => 1
                ]
            ),
            'three_d_secure' => (bool) $method->getProviderConfig()->getValue('three_d_secure'),
        ]);

        $this->jsLayout['components']['mpgs-checkout-review-place-order']['config'] = $config;
        return parent::getJsLayout();
    }
}
