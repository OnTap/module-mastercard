<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\MasterCard\Gateway\Response\Hosted;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;

class SessionHandler implements HandlerInterface
{
    const SUCCESS_INDICATOR = 'successIndicator';

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

        $session = $response['session'];
        $payment->setAdditionalInformation('session', [
            'id' => $session['id'],
            'version' => $session['version'],
            static::SUCCESS_INDICATOR => $response[static::SUCCESS_INDICATOR] //@todo remove this
        ]);
        $payment->setAdditionalInformation(static::SUCCESS_INDICATOR, $response[static::SUCCESS_INDICATOR]);
    }
}
