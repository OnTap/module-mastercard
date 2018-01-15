<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;
use OnTap\MasterCard\Model\Method\WalletInterface;

class SessionDataBuilder implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        // Session is only relevant with Wallets
        if (!($payment->getMethodInstance() instanceof WalletInterface)) {
            return [];
        }

//        ContextHelper::assertOrderPayment($payment);

        $session = $payment->getAdditionalInformation('session');

        return [
            'session' => [
                'id' => $session['id'],
                'version' => $session['version']
            ]
        ];
    }
}
