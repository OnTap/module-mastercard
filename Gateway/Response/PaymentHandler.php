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

namespace OnTap\MasterCard\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Helper\ContextHelper;

class PaymentHandler implements HandlerInterface
{
    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        static::importPaymentResponse($payment, $response);
    }

    /**
     * @param array $data
     * @param string $field
     * @return string|null
     */
    // @codingStandardsIgnoreStart
    public static function safeValue($data, $field)
    // @codingStandardsIgnoreStop
    {
        return isset($data[$field]) ? $data[$field] : null;
    }


    /**
     * @param Payment $payment
     * @param array $response
     * @throws LocalizedException
     */
    // @codingStandardsIgnoreStart
    public static function importPaymentResponse(Payment $payment, $response)
    // @codingStandardsIgnoreStop
    {
        $payment->setAdditionalInformation('gateway_code', $response['response']['gatewayCode']);
        $payment->setAdditionalInformation('txn_result', $response['result']);

        if (isset($response['transaction'])) {
            $payment->setAdditionalInformation('transaction', $response['transaction']);
            if (isset($response['transaction']['authorizationCode'])) {
                $payment->setAdditionalInformation('auth_code', $response['transaction']['authorizationCode']);
            }
        }

        if (isset($response['risk'])) {
            $payment->setAdditionalInformation('risk', $response['risk']);
        }

        if (isset($response['sourceOfFunds']) && isset($response['sourceOfFunds']['provided']['card'])) {
            $cardDetails = $response['sourceOfFunds']['provided']['card'];

            $payment->setAdditionalInformation('card_scheme', $cardDetails['scheme']);
            $payment->setAdditionalInformation(
                'card_number',
                'XXXX-' . substr($cardDetails['number'], -4)
            );
            $payment->setAdditionalInformation(
                'card_expiry_date',
                sprintf(
                    '%s/%s',
                    $cardDetails['expiry']['month'],
                    $cardDetails['expiry']['year']
                )
            );
            if (isset($cardDetails['fundingMethod'])) {
                $payment->setAdditionalInformation('fundingMethod', static::safeValue($cardDetails, 'fundingMethod'));
            }
            if (isset($cardDetails['issuer'])) {
                $payment->setAdditionalInformation('issuer', static::safeValue($cardDetails, 'issuer'));
            }
            if (isset($cardDetails['nameOnCard'])) {
                $payment->setAdditionalInformation('nameOnCard', static::safeValue($cardDetails, 'nameOnCard'));
            }
        }

        if (isset($response['response']['cardSecurityCode'])) {
            $payment->setAdditionalInformation(
                'cvv_validation',
                $response['response']['cardSecurityCode']['gatewayCode']
            );
        }
    }
}
