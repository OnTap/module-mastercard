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

namespace OnTap\MasterCard\Gateway\Request\Ach;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;

class SessionDataBuilder implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        return [
            'session' => [
                'id' => $payment->getAdditionalInformation('session_id')
            ],
            'sourceOfFunds' => [
                'type' => 'ACH',
                'provided' => [
                    'ach' => [
                        'secCode' => 'WEB'
                    ]
                ]
            ]
        ];
    }
}
