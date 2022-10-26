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
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use OnTap\MasterCard\Gateway\Config\Config;
use OnTap\MasterCard\Gateway\Http\Client\Rest;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $httpMethod = Rest::PUT;

    /**
     * @var TransferBuilder
     */
    protected $transferBuilder;

    /**
     * @var array
     */
    protected $request = [];

    /**
     * @param Config $config
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        Config $config,
        TransferBuilder $transferBuilder
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
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
     * @param null $storeId
     *
     * @return string
     */
    protected function apiVersionUri($storeId = null)
    {
        return 'version/' . $this->config->getValue('api_version', $storeId) . '/';
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    protected function merchantUri($storeId = null)
    {
        return 'merchant/' . $this->config->getMerchantId($storeId) . '/';
    }

    /**
     * Generate a new transactionId
     *
     * @param PaymentDataObjectInterface $payment
     *
     * @return string
     */
    protected function createTxnId(PaymentDataObjectInterface $payment)
    {
        return uniqid(sprintf('%s-', (string)$payment->getOrder()->getOrderIncrementId()));
    }

    /**
     * @param int|null $storeId
     *
     * @return mixed
     */
    protected function getGatewayUri($storeId = null)
    {
        return $this->config->getApiUrl($storeId) . $this->apiVersionUri($storeId) . $this->merchantUri($storeId);
    }

    /**
     * @param PaymentDataObjectInterface $payment
     *
     * @return string
     */
    protected function getUri(PaymentDataObjectInterface $payment)
    {
        $orderId = $payment->getOrder()->getOrderIncrementId();
        $txnId = $this->request['transaction']['reference'] ?? $this->createTxnId($payment);
        $storeId = $payment->getOrder()->getStoreId();

        return $this->getGatewayUri($storeId) . 'order/' . $orderId . '/transaction/' . $txnId;
    }

    /**
     * @return string[]
     */
    protected function getMethodHeaders(): array
    {
        return [
            'Content-Type' => 'application/json;charset=UTF-8',
        ];
    }

    /**
     * @param array $request
     * @param PaymentDataObjectInterface $payment
     *
     * @return TransferInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function create(array $request, PaymentDataObjectInterface $payment)
    {
        $this->request = $request;
        $storeId = $payment->getOrder()->getStoreId();

        $builder = $this->transferBuilder
            ->setMethod($this->httpMethod)
            ->setHeaders($this->getMethodHeaders())
            ->setBody($request)
            ->setUri($this->getUri($payment));

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
