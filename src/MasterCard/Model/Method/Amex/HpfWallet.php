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
            'three_d_secure' => (bool) $this->getProviderConfig()->getValue('three_d_secure'),
            'check_url' => $this->getUrlBuilder()->getUrl(
                'tns/threedsecure/check',
                [
                    'method' => 'tns_hpf_amex',
                    '_secure' => 1
                ]
            ),
        ];
    }
}
