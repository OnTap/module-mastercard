<?php
/**
 * Copyright (c) 2016-2022 Mastercard
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

namespace OnTap\MasterCard\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Helper\Data as PaymentDataHelper;
use Magento\Store\Model\StoreManagerInterface;
use OnTap\MasterCard\Model\CertFactory;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const WEB_HOOK_RESPONSE_URL = 'tns/webhook/response';
    const API_EUROPE = 'api_eu';
    const API_AMERICA = 'api_na';
    const API_ASIA = 'api_as';
    const API_INDIA = 'api_in';
    const API_OTHER = 'api_other';
    const TEST_PREFIX = 'TEST';
    const AUTHENTICATION_TYPE_PASSWORD = 'password';
    const AUTHENTICATION_TYPE_CERTIFICATE = 'certificate';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $paymentDataHelper;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var CertFactory
     */
    protected $certFactory;

    /**
     * @var string
     */
    protected $methodCode;

    /**
     * @var string
     */
    protected $pathPattern;

    /**
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param CertFactory $certFactory
     * @param PaymentDataHelper $paymentDataHelper
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        CertFactory $certFactory,
        PaymentDataHelper $paymentDataHelper,
        $methodCode = '',
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->certFactory = $certFactory;
        $this->methodCode = $methodCode;
        $this->pathPattern = $pathPattern;
        $this->paymentDataHelper = $paymentDataHelper;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getMerchantId($storeId = null)
    {
        $currentMerchantId = $this->getValue('api_username', $storeId);
        if ($this->getValue('test', $storeId)) {
            if (strpos($currentMerchantId, self::TEST_PREFIX) === 0) {
                return $this->getValue('api_username', $storeId);
            } else {
                return self::TEST_PREFIX . $this->getValue('api_username', $storeId);
            }
        } else {
            return $this->getValue('api_username', $storeId);
        }
    }

    /**
     * @param null $storeId
     *
     * @return mixed|null
     */
    public function isTestMode($storeId = null)
    {
        return $this->getValue('test', $storeId);
    }

    /**
     * @param string|int|null $storeId
     *
     * @return string
     */
    public function getMerchantPassword($storeId = null)
    {
        return $this->getValue('api_password', $storeId);
    }

    /**
     * @param string|int|null $storeId
     *
     * @return bool
     */
    public function isCertificateAutherntification($storeId = null)
    {
        return $this->getValue('authentication_type', $storeId) === self::AUTHENTICATION_TYPE_CERTIFICATE;
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getFrontendAreaUrl($storeId = null)
    {
        if ($this->getValue('api_gateway', $storeId) === self::API_OTHER) {
            $url = $this->getValue('api_gateway_other', $storeId);
            if (empty($url)) {
                return '';
            }
            if (substr($url, -1) !== '/') {
                $url .= '/';
            }

            return $url;
        }

        return $this->getValue(
            $this->getValue('api_gateway', $storeId),
            $storeId
        );
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getApiAreaUrl($storeId = null)
    {
        $pkiPostfix = $this->isCertificateAutherntification($storeId) ? '_pki' : '';

        if ($this->getValue('api_gateway', $storeId) === self::API_OTHER) {
            $url = $this->getValue('api_gateway_other' . $pkiPostfix, $storeId);
            if (empty($url)) {
                return '';
            }
            if (substr($url, -1) !== '/') {
                $url .= '/';
            }

            return $url;
        }

        return $this->getValue(
            $this->getValue('api_gateway', $storeId) . $pkiPostfix,
            $storeId
        );
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getApiUrl($storeId = null)
    {
        return $this->getApiAreaUrl($storeId) . 'api/rest/';
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getWebhookSecret($storeId = null)
    {
        return $this->getValue('webhook_secret', $storeId);
    }

    /**
     * @param null|int $storeId
     *
     * @return mixed|null|string
     */
    public function getWebhookNotificationUrl($storeId = null)
    {
        if ($this->getWebhookSecret($storeId) && $this->getWebhookSecret($storeId) === "") {
            return null;
        }
        if ($this->getValue('webhook_url', $storeId) && $this->getValue('webhook_url', $storeId) !== "") {
            return $this->getValue('webhook_url', $storeId);
        }

        return $this->urlBuilder->getUrl(static::WEB_HOOK_RESPONSE_URL, ['_secure' => true]);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isSendLineItems($storeId = null)
    {
        return (bool)$this->getValue('send_line_items', $storeId);
    }

    /**
     * @param int|string|null $storeId
     *
     * @return string|null
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getSSLCertificatePath($storeId = null)
    {
        if (!$this->getValue('api_cert', $storeId)) {
            return null;
        }

        $path = sprintf($this->pathPattern, $this->methodCode, 'api_cert');
        $websiteId = $this->getWebsiteId($storeId);

        return $this->certFactory
            ->create()
            ->loadByPathAndWebsite($path, $websiteId, false)
            ->getCertPath();
    }

    /**
     * @param int|string|null $storeId
     *
     * @return string|null
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getSSLKeyPath($storeId = null)
    {
        if (!$this->getValue('api_key', $storeId)) {
            return null;
        }

        $path = sprintf($this->pathPattern, $this->methodCode, 'api_key');
        $websiteId = $this->getWebsiteId($storeId);

        return $this->certFactory
            ->create()
            ->loadByPathAndWebsite($path, $websiteId, false)
            ->getCertPath();
    }

    /**
     * @param int|string|null $storeId
     *
     * @return int
     *
     * @throws NoSuchEntityException
     */
    protected function getWebsiteId($storeId)
    {
        return $this->storeManager->getStore($storeId)->getWebsiteId();
    }
}
