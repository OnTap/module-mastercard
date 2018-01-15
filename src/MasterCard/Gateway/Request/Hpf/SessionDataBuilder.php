<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request\Hpf;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class SessionDataBuilder implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws \Zend_Json_Exception
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $session = $payment->getAdditionalInformation('session');

        // By default Magento behaviour, the additional_data can only be saves as string[]
        // this process helps to solve that
        if (is_string($session)) {
            $session = \Zend_Json::decode($session);
        }

        return [
            'session' => [
                'id' => $session['id'],
                'version' => $session['version']
            ],
        ];
    }
}
