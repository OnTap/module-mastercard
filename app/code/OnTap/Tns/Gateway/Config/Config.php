<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const WEB_HOOK_RESPONSE_URL = 'tns/webhook/response';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Config constructor.
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        $methodCode = '',
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->urlBuilder = $urlBuilder;
    }

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

    /**
     * @return string|null
     */
    public function getWebhookNotificationUrl()
    {
        if ($this->getWebhookSecret() === "") {
            return null;
        }
        if ($this->getValue('webhook_url') && $this->getValue('webhook_url') !== "") {
            return $this->getValue('webhook_url');
        }
        return $this->urlBuilder->getUrl(static::WEB_HOOK_RESPONSE_URL, ['_secure' => true]);
    }
}
