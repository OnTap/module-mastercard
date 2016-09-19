<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Config\Hpf;

use OnTap\MasterCard\Model\Ui\Hpf\ConfigProvider;

class Config extends \OnTap\MasterCard\Gateway\Config\Config
{
    const COMPONENT_URI = '%sform/version/%s/merchant/%s/session.js';

    /**
     * @var string
     */
    protected $method = 'tns_hpf';

    /**
     * @return \Magento\Payment\Model\MethodInterface
     */
    protected function getVaultPayment()
    {
        return $this->getPaymentDataHelper()->getMethodInstance(ConfigProvider::CC_VAULT_CODE);
    }

    /**
     * @return string
     */
    public function getComponentUrl()
    {
        return sprintf(
            static::COMPONENT_URI,
            $this->getApiAreaUrl(),
            $this->getValue('api_version'),
            $this->getMerchantId()
        );
    }
}
