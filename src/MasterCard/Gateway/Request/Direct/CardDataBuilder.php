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

namespace OnTap\MasterCard\Gateway\Request\Direct;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;

class CardDataBuilder implements BuilderInterface
{
    const TYPE = 'CARD';
    const CC_NUMBER = 'cc_number';
    const CC_TYPE = 'cc_type';
    const CC_EXP_YEAR = 'cc_exp_year';
    const CC_EXP_MONTH = 'cc_exp_month';
    const CC_CID = 'cc_cid';

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
            'sourceOfFunds' => [
                'provided' => [
                    'card' => [
                        'expiry' => [
                            'month' => $this->formatMonth($payment->getAdditionalInformation(self::CC_EXP_MONTH)),
                            'year' => $this->formatYear($payment->getAdditionalInformation(self::CC_EXP_YEAR)),
                        ],
                        'number' => $payment->getAdditionalInformation(self::CC_NUMBER),
                        'securityCode' => $payment->getAdditionalInformation(self::CC_CID),
                    ],
                ],
                'type' => self::TYPE,
            ],
        ];
    }

    /**
     * @param string $month
     * @return null|string
     */
    protected function formatMonth($month)
    {
        return !empty($month) ? sprintf('%02d', $month) : null;
    }

    /**
     * @param string $year
     * @return null|string
     */
    protected function formatYear($year)
    {
        return !empty($year) ? substr($year, -2, 2) : null;
    }
}
