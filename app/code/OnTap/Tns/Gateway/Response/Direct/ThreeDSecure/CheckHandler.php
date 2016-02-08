<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Response\Direct\ThreeDSecure;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class CheckHandler implements HandlerInterface
{
    const THREEDSECURE_CHECK = '3DSecureEnrollment';

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

        // @todo: remove these params when done with them
        $tdsAuth = $response['3DSecure']['authenticationRedirect']['customized'];

        $payment->setAdditionalInformation(static::THREEDSECURE_CHECK, [
            'acsUrl' =>  $tdsAuth['acsUrl'],
            'paReq' => $tdsAuth['paReq'],
            'status' => $response['3DSecure']['summaryStatus'],
            'xid' => $response['3DSecure']['xid'],
        ]);
        $payment->setAdditionalInformation('3DSecureId', $response['3DSecureId']);
        $payment->save();
    }
}
