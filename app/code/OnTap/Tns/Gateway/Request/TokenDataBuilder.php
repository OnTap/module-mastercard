<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;

class TokenDataBuilder implements BuilderInterface
{
    const TYPE = 'CARD';

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

        $extensionAttributes = $payment->getExtensionAttributes();
        $paymentToken = $extensionAttributes->getVaultPaymentToken();

        return [
            'sourceOfFunds' => [
                'token' => $paymentToken->getGatewayToken(),
                'type' => static::TYPE,
            ],
        ];
    }
}
