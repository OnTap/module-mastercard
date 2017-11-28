<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Controller\Session;

class UpdateMasterpass extends UpdateWallet
{
    /**
     * @return string
     */
    protected function getMethod()
    {
        return 'tns_direct_masterpass';
    }
}
