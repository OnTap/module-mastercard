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

declare(strict_types=1);

namespace OnTap\MasterCard\Gateway\Response\Authentication;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class AuthenticatePayerHandler implements HandlerInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * InitiateAuthHandler constructor.
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        $payment->setAdditionalInformation(
            'auth_payment_interaction',
            $this->arrayManager->get('authentication/payerInteraction', $response)
        );
        $payment->setAdditionalInformation(
            'auth_redirect_html',
            $this->arrayManager->get('authentication/redirectHtml', $response)
        );
        $payment->setAdditionalInformation('result', $this->arrayManager->get('result', $response));
        $payment->setAdditionalInformation(
            'response_gateway_recommendation',
            $this->arrayManager->get('response/gatewayRecommendation', $response)
        );
        $payment->setAdditionalInformation('transaction_type', $this->arrayManager->get('transaction/type', $response));
    }
}
