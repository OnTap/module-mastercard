<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Model\Method\Amex;

use OnTap\MasterCard\Model\Method\WalletInterface;

class DirectWallet extends \OnTap\MasterCard\Model\Method\Wallet implements WalletInterface
{
    protected $walletInitializeCommand = 'amex_create_session';

    /**
     * @return array
     */
    public function getJsConfig()
    {
        return [
            'adapter_component' => $this->getMethodConfig()->getValue('adapter_component'),
            'client_id' => $this->getConfigData('client_id'),
            'env' => $this->getConfigData('env'),
            'callback_url' => $this->getUrlBuilder()->getUrl('mpgs/wallet/amexDirect'),
            'three_d_secure' => (bool) $this->getProviderConfig()->getValue('three_d_secure'),
            'check_url' => $this->getUrlBuilder()->getUrl(
                'tns/threedsecure/check',
                [
                    'method' => 'amex',
                    '_secure' => 1
                ]
            ),
        ];
    }
}
