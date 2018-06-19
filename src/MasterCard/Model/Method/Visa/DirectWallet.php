<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Model\Method\Visa;

use OnTap\MasterCard\Model\Method\WalletInterface;

class DirectWallet extends \OnTap\MasterCard\Model\Method\Wallet implements WalletInterface
{
    /**
     * @return array
     */
    public function getJsConfig()
    {
        return [
        ];
    }
}
