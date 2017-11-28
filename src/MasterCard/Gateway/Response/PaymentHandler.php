<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Helper\ContextHelper;

class PaymentHandler implements HandlerInterface
{
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
        ContextHelper::assertOrderPayment($payment);

        static::importPaymentResponse($payment, $response);
    }

    /**
     * @param Payment|\Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @param array $response
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function importPaymentResponse(Payment $payment, $response)
    {
        $payment->setAdditionalInformation('gateway_code', $response['response']['gatewayCode']);
        $payment->setAdditionalInformation('txn_result', $response['result']);

        if (isset($response['transaction'])) {
            $payment->setAdditionalInformation('transaction', $response['transaction']);
            if (isset($response['transaction']['authorizationCode'])) {
                $payment->setAdditionalInformation('auth_code', $response['transaction']['authorizationCode']);
            }
        }

        if (isset($response['risk'])) {
            $payment->setAdditionalInformation('risk', $response['risk']);
        }
    }
}
