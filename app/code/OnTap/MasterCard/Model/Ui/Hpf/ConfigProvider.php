<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\MasterCard\Model\Ui\Hpf;

use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'tns_hpf';
    const CC_VAULT_CODE = 'tns_hpf_vault';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param UrlInterface $urlBuilder
     */
    public function __construct(ConfigInterface $config, UrlInterface $urlBuilder)
    {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::METHOD_CODE => [
                    'merchant_username' => $this->config->getMerchantId(),
                    'component_url' => $this->config->getComponentUrl(),
                    'debug' => (bool) $this->config->getValue('debug'),
                    'three_d_secure' => (bool) $this->config->getValue('three_d_secure'),
                    'ccVaultCode' => static::CC_VAULT_CODE
                ]
            ]
        ];
    }
}
