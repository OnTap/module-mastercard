<?php
/**
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use OnTap\MasterCard\Gateway\Request\Direct\CardDataBuilder;

class CscDataBuilder implements BuilderInterface
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
        $csc = $payment->getAdditionalInformation(CardDataBuilder::CC_CID);

        if (!$csc) {
            return [];
        }

        return [
            'sourceOfFunds' => [
                'provided' => [
                    'card' => [
                        'securityCode' => $payment->getAdditionalInformation(CardDataBuilder::CC_CID)
                    ]
                ]
            ]
        ];
    }
}
