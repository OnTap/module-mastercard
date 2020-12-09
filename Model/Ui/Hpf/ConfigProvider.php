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
                    'three_d_secure_version' => (int) $this->config->getValue('three_d_secure'),
                    'ccVaultCode' => static::CC_VAULT_CODE,
                    'check_url' => $this->urlBuilder->getUrl(
                        'tns/threedsecure/check',
                        [
                            'method' => 'hpf',
                            '_secure' => 1
                        ]
                    ),
                ]
            ]
        ];
    }
}
