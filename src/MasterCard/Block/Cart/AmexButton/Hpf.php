<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Block\Cart\AmexButton;

use OnTap\MasterCard\Block\Cart\AmexButton;

class Hpf extends AmexButton
{
    /**
     * @return string
     */
    public function getJsComponent()
    {
        return $this->method->getProviderConfig()->getComponentUrl();
    }

    /**
     * @return string
     */
    public function getComponentConfig()
    {
        $quote = $this->session->getQuote();

        $config = [
            'frameEmbeddingMitigation' => ['x-frame-options'],
            'order' => [
                'amount' => $quote->getGrandTotal(),
                'currency' => $quote->getStoreCurrencyCode()
            ],
            'wallets' => [
                'amexExpressCheckout' => [
                    'enabled' => true,
                    'initTags' => [
                        'theme' => 'responsive',
                        'env' => 'qa',
                        'disable_btn' => false,
                        'client_id' => $this->method->getConfigData('client_id')
                    ]
                ]
            ]
        ];
        return \Zend_Json_Encoder::encode($config);
    }
}
