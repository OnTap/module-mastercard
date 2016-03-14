<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Config;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    /**
     * @return string
     */
    public function getMerchantId()
    {
        // @todo: sandbox switch
        return $this->getValue('api_test_username');
    }

    /**
     * @return string
     */
    public function getMerchantPassword()
    {
        // @todo: sandbox switch
        return $this->getValue('api_test_password');
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        // @todo: sandbox switch
        return $this->getValue('api_test_url');
    }

    /**
     * @return string
     */
    public function getWebhookSecret()
    {
        // @todo: sandbox switch
        return $this->getValue('webhook_test_secret');
    }
}
