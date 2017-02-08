<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Payment\Helper\Data;
use Magento\Framework\App\ObjectManager;
use OnTap\MasterCard\Model\Ui\Direct\ConfigProvider;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const WEB_HOOK_RESPONSE_URL = 'tns/webhook/response';
    const API_EUROPE = 'api_eu';
    const API_AMERICA = 'api_na';
    const API_ASIA = 'api_as';
    const API_UAT = 'api_uat';
    const API_OTHER = 'api_other';
    const TEST_PREFIX = 'TEST';

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
    protected $method = 'tns_direct';

    /**
     * Config constructor.
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        $methodCode = '',
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return bool
     */
    public function isVaultEnabled()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $vaultPayment = $this->getVaultPayment();
        return $vaultPayment->isActive($storeId);
    }

    /**
     * @return \Magento\Payment\Model\MethodInterface
     */
    protected function getVaultPayment()
    {
        return $this->getPaymentDataHelper()->getMethodInstance(ConfigProvider::CC_VAULT_CODE);
    }

    /**
     * @return Data
     */
    protected function getPaymentDataHelper()
    {
        if ($this->paymentDataHelper === null) {
            $this->paymentDataHelper = ObjectManager::getInstance()->get(Data::class);
        }
        return $this->paymentDataHelper;
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        if ((bool) $this->getValue('test')) {
            return static::TEST_PREFIX . $this->getValue('api_username');
        } else {
            return $this->getValue('api_username');
        }
    }

    /**
     * @return string
     */
    public function getMerchantPassword()
    {
        return $this->getValue('api_password');
    }

    /**
     * @return string
     */
    public function getApiAreaUrl()
    {
        if ($this->getValue('api_gateway') == self::API_OTHER) {
            $url = $this->getValue('api_gateway_other');
            if (empty($url)) {
                return '';
            }
            if (substr($url, -1) !== '/') {
                $url = $url . '/';
            }
            return $url;
        }
        return $this->getValue($this->getValue('api_gateway'));
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->getApiAreaUrl() . 'api/rest/';
    }

    /**
     * @return string
     */
    public function getWebhookSecret()
    {
        return $this->getValue('webhook_secret');
    }

    /**
     * @return mixed|null|string
     */
    public function getWebhookNotificationUrl()
    {
        if ($this->getWebhookSecret() && $this->getWebhookSecret() === "") {
            return null;
        }
        if ($this->getValue('webhook_url') && $this->getValue('webhook_url') !== "") {
            return $this->getValue('webhook_url');
        }
        return $this->urlBuilder->getUrl(static::WEB_HOOK_RESPONSE_URL, ['_secure' => true]);
    }
}
