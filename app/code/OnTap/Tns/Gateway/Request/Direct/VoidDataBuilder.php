<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Request\Direct;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class VoidDataBuilder implements BuilderInterface
{
    const OPERATION = 'VOID';

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        return [
            'apiOperation' => self::OPERATION,
            'transaction' => [
                'targetTransactionId' => $paymentDO->getPayment()->getParentTransactionId()
            ]
        ];
    }
}
