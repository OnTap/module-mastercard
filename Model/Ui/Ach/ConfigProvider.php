<?php
/**
 * Copyright (c) 2016-2019 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OnTap\MasterCard\Model\Ui\Ach;

use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use OnTap\MasterCard\Model\Config\Source\Integration;

class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'mpgs_ach';
    const SESSION_COMPONENT_URI = '%sform/version/%s/merchant/%s/session.js';
    const CHECKOUT_COMPONENT_URI = '%scheckout/version/%s/checkout.js';

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
        $config = [
            'merchant_username' => $this->config->getMerchantId(),
            'integration_mode' => $this->config->getValue('integration'),
        ];

        $mode = $this->config->getValue('integration') ?? Integration::HOSTED_CHECKOUT;

        $integrationConfig = [
            Integration::HOSTED_CHECKOUT => [
                'renderer' => 'hosted-checkout',
                'component_url' => sprintf(
                    static::CHECKOUT_COMPONENT_URI,
                    $this->config->getApiAreaUrl(),
                    $this->config->getValue('api_version')
                )
            ],
            Integration::HOSTED_SESSION => [
                'renderer' => 'hosted-session',
                'component_url' => sprintf(
                    static::SESSION_COMPONENT_URI,
                    $this->config->getApiAreaUrl(),
                    $this->config->getValue('api_version'),
                    $this->config->getMerchantId()
                )
            ]
        ];
        return [
            'payment' => [
                self::METHOD_CODE => array_merge($config, $integrationConfig[$mode])
            ]
        ];
    }
}
