<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Config\Hosted;

class Config extends \OnTap\MasterCard\Gateway\Config\Config
{
    const COMPONENT_URI = '%scheckout/version/%s/checkout.js';

    /**
     * @var string
     */
    protected $method = 'tns_hosted';

    /**
     * @return string
     */
    public function getComponentUrl()
    {
        return sprintf(
            static::COMPONENT_URI,
            $this->getApiAreaUrl(),
            $this->getValue('api_version')
        );
    }

    /**
     * @return bool
     */
    public function isVaultEnabled()
    {
        return false;
    }
}
