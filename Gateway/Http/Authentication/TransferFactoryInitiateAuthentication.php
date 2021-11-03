<?php
/**
 * Copyright (c) 2016-2020 Mastercard
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

namespace OnTap\MasterCard\Gateway\Http\Authentication;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use OnTap\MasterCard\Gateway\Http\TransferFactory;

class TransferFactoryInitiateAuthentication extends TransferFactory
{
    /**
     * @param PaymentDataObjectInterface $payment
     * @return string
     */
    protected function getUri(PaymentDataObjectInterface $payment)
    {
        $orderId = $payment->getOrder()->getOrderIncrementId();
        $transactionId = $this->request['transaction']['reference'] ?? $payment->getPayment()->getAdditionalInformation('auth_init_transaction_id');
        if (!$transactionId || explode('-', $transactionId)[0] !== $orderId) {
            $transactionId = $this->createTxnId($payment);
        }
        $storeId = $payment->getOrder()->getStoreId();

        return $this->getGatewayUri($storeId) . 'order/' . $orderId . '/transaction/' . $transactionId;
    }
}
