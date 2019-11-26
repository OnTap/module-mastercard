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

namespace OnTap\MasterCard\Gateway\Http;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class GetTransferFactory extends TransferFactory
{
    /**
     * @var string
     */
    protected $httpMethod = 'GET';

    /**
     * @param array $request
     * @param int|null $storeId
     * @return string
     */
    protected function getRequestUri($request, $storeId = null)
    {
        $orderId = $request['order_id'];
        $txnId = $request['transaction_id'];
        return $this->getGatewayUri($storeId) . 'order/' . $orderId . '/transaction/' . $txnId;
    }

    /**
     * @param array $request
     * @param PaymentDataObjectInterface $payment
     * @return TransferInterface
     */
    public function create(array $request, PaymentDataObjectInterface $payment)
    {
        $storeId = $payment->getOrder()->getStoreId();
        return $this->transferBuilder
            ->setMethod($this->httpMethod)
            ->setHeaders(['Content-Type' => 'application/json;charset=UTF-8'])
            ->setBody([])
            ->setAuthUsername($this->getMerchantUsername($storeId))
            ->setAuthPassword($this->config->getMerchantPassword($storeId))
            ->setUri($this->getRequestUri($request, $storeId))
            ->build();
    }
}
