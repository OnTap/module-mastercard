<?php
/**
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Config;

class VaultConfigProvider
{
    /**
     * @var array
     */
    protected $config;

    /**
     * VaultConfigProvider constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * @param string $methodCode
     * @return \OnTap\MasterCard\Gateway\Config\Config
     */
    public function getConfig($methodCode)
    {
        return $this->config[$methodCode];
    }
}
