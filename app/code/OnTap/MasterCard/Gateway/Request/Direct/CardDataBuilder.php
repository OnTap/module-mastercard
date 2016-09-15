<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
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
