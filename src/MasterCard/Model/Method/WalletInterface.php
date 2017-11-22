<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Model\Method;

use Magento\Payment\Model\MethodInterface;

interface WalletInterface extends \Magento\Payment\Model\MethodInterface
{
    /**
     * @return string
     */
    public function getProviderCode();

    /**
     * @return MethodInterface
     */
    public function getProvider();

    /**
     * @return array
     */
    public function getJsConfig();
}
