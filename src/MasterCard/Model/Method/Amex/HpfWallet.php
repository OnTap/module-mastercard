<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Model\Method\Amex;

use OnTap\MasterCard\Model\Method\WalletInterface;

class HpfWallet extends \OnTap\MasterCard\Model\Method\Wallet implements WalletInterface
{
    /**
     * @return array
     */
    public function getJsConfig()
    {
        return [
            'merchant_username' => $this->getProviderConfig()->getMerchantId(),
            'component_url' => $this->getProviderConfig()->getComponentUrl(),
            'debug' => (bool) $this->getProviderConfig()->getValue('debug'),
            'client_id' => $this->getConfigData('client_id'),
            'env' => $this->getConfigData('env'),
            'callback_url' => $this->getUrlBuilder()->getUrl('mpgs/wallet/amex', ['_secure' => true]),
            'three_d_secure' => (bool) $this->getProviderConfig()->getValue('three_d_secure'),
            'check_url' => $this->getUrlBuilder()->getUrl(
                'tns/threedsecure/check',
                [
                    'method' => 'amex_hpf',
                    '_secure' => 1
                ]
            ),
        ];
    }
}
