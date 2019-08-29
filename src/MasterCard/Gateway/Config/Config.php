<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Payment\Model\MethodInterface;
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
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isVaultEnabled()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $vaultPayment = $this->getVaultPayment();
        return $vaultPayment->isActive($storeId);
    }

    /**
     * @return MethodInterface
     * @throws LocalizedException
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
     * @param null $storeId
     * @return string
     */
    public function getMerchantId($storeId = null)
    {
        if ((bool) $this->getValue('test', $storeId)) {
            return static::TEST_PREFIX . $this->getValue('api_username', $storeId);
        } else {
            return $this->getValue('api_username', $storeId);
        }
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getMerchantPassword($storeId = null)
    {
        return $this->getValue('api_password', $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getApiAreaUrl($storeId = null)
    {
        if ($this->getValue('api_gateway', $storeId) == self::API_OTHER) {
            $url = $this->getValue('api_gateway_other', $storeId);
            if (empty($url)) {
                return '';
            }
            if (substr($url, -1) !== '/') {
                $url = $url . '/';
            }
            return $url;
        }
        return $this->getValue($this->getValue('api_gateway', $storeId), $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getApiUrl($storeId = null)
    {
        return $this->getApiAreaUrl($storeId) . 'api/rest/';
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getWebhookSecret($storeId = null)
    {
        return $this->getValue('webhook_secret', $storeId);
    }

    /**
     * @param null $storeId
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
     * @param null $storeId
     * @return array
     */
    public function getVaultConfig($storeId = null)
    {
        return [
            'useCcv' => (bool) $this->getValue('vault_ccv', $storeId)
        ];
    }

    /**
     * @return string
     */
    public function getVaultComponent()
    {
        return 'OnTap_MasterCard/js/view/payment/method-renderer/direct-vault';
    }
}
