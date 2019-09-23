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

    /**
     * @param null $storeId
     * @return array
     */
    public function getVaultConfig($storeId = null)
    {
        return [
            'component_url' => $this->getComponentUrl(),
            'useCcv' => (bool) $this->getValue('vault_ccv', $storeId),
        ];
    }

    /**
     * @return string
     */
    public function getVaultComponent()
    {
        return 'OnTap_MasterCard/js/view/payment/method-renderer/hpf-vault';
    }
}
