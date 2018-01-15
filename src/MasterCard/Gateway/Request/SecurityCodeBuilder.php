<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class SecurityCodeBuilder implements BuilderInterface
{
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

        return [
            'sourceOfFunds' => [
                'provided' => [
                    'card' => [
                        'securityCode' => $payment->getAdditionalInformation(self::CC_CID)
                    ]
                ]
            ]
        ];
    }
}
