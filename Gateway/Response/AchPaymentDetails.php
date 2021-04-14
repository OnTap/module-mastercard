<?php
/**
 * Copyright (c) 2016-2021 Mastercard
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

namespace OnTap\MasterCard\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class AchPaymentDetails implements HandlerInterface
{
    const ACCOUNT_TYPE = 'accountType';
    const ACCOUNT_HOLDER = 'bankAccountHolder';
    const ACCOUNT_NUMBER = 'bankAccountNumber';
    const ROUTING_NUMBER = 'routingNumber';
    const SEC_CODE = 'secCode';

    /**
     * @var string[]
     */
    protected $additionalAccountInfo = [
        self::ACCOUNT_TYPE,
        self::ACCOUNT_HOLDER,
        self::ACCOUNT_NUMBER,
        self::ROUTING_NUMBER,
        self::SEC_CODE
    ];

    /**
     * @inheridoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var PaymentDataObject $payment */
        $payment = SubjectReader::readPayment($handlingSubject);

        /** @var OrderPaymentInterface $payment */
        $payment = $payment->getPayment();

        $payment->setLastTransId($response['transaction']['id']);

        // @todo: multistep_ach
        // Set transaction as pending because ACH does not capture it immediately
        // Would use this in case we need to split the process between APPROVED_PENDING_SETTLEMENT (realtime) and APPROVED (webhook)
        //$isPending = $response['response']['gatewayCode'] === 'APPROVED_PENDING_SETTLEMENT';
        $isPending = false;
        $payment->setIsTransactionPending($isPending == true);
        $payment->setIsTransactionClosed($isPending != true);

        $sourceOfFunds = $response['sourceOfFunds']['provided']['ach'];
        $additionalInfo = [];
        foreach ($this->additionalAccountInfo as $item) {
            if (!isset($sourceOfFunds[$item])) {
                continue;
            }
            $additionalInfo[$item] = $sourceOfFunds[$item];
        }

        $additionalInfo['gateway_code'] = $response['response']['gatewayCode'];
        $additionalInfo['txn_result'] = $response['result'];

        $payment->setAdditionalInformation($additionalInfo);
    }
}
