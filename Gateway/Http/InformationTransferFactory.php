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

namespace OnTap\MasterCard\Gateway\Http;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use OnTap\MasterCard\Gateway\Config\Config;
use OnTap\MasterCard\Model\SelectedStore;

class InformationTransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var SelectedStore
     */
    protected $selectedStore;

    /**
     * @param TransferBuilder $transferBuilder
     * @param Config $config
     * @param SelectedStore $selectedStore
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        Config $config,
        SelectedStore $selectedStore
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config = $config;
        $this->selectedStore = $selectedStore;
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    protected function getMerchantUsername($storeId = null)
    {
        return 'merchant.' . $this->config->getMerchantId($storeId);
    }

    /**
     * @param array $request
     *
     * @return TransferInterface
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function create(array $request)
    {
        $storeId = $this->selectedStore->getStoreId();
        $version = $this->config->getValue('api_version');
        $merchantId = $this->config->getMerchantId($storeId);

        $builder = $this->transferBuilder
            ->setMethod('GET')
            ->setHeaders(['Content-Type' => 'application/json;charset=UTF-8'])
            ->setBody($request)
            ->setUri(
                $this->config->getApiUrl($storeId)
                . 'version/' . $version
                . '/merchant/' . $merchantId
                . '/paymentOptionsInquiry'
            );

        if ($this->config->isCertificateAutherntification($storeId)) {
            $builder->setClientConfig([
                CURLOPT_SSLCERT => $this->config->getSSLCertificatePath($storeId),
                CURLOPT_SSLKEY => $this->config->getSSLKeyPath($storeId),
            ]);
        } else {
            $userPassword = $this->getMerchantUsername($storeId) . ":" . $this->config->getMerchantPassword($storeId);
            $builder->setClientConfig([
                CURLOPT_USERPWD => $userPassword,
            ]);
        }

        return $builder->build();
    }
}
