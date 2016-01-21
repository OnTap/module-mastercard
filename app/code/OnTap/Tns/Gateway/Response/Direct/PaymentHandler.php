<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Response\Direct;

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

        $payment->setTransactionId($response['transaction']['id']);
        $payment->setLastTransId($response['transaction']['id']);
        $payment->setIsTransactionClosed(false);

        $payment->setAdditionalInformation('gateway_code', $response['response']['gatewayCode']);
        $payment->setAdditionalInformation('txn_result', $response['result']);

        if (isset($response['sourceOfFunds']) && isset($response['sourceOfFunds']['provided']['card'])) {
            $cardDetails = $response['sourceOfFunds']['provided']['card'];

            $payment->setAdditionalInformation('card_scheme', $cardDetails['scheme']);
            $payment->setAdditionalInformation(
                'card_number',
                'XXXX-' . substr($cardDetails['number'], -4)
            );
            $payment->setAdditionalInformation(
                'card_expiry_date',
                sprintf(
                    '%s/%s',
                    $cardDetails['expiry']['month'],
                    $cardDetails['expiry']['year']
                )
            );
        }

        if (isset($response['response']['cardSecurityCode'])) {
            $payment->setAdditionalInformation('cvv_validation',
                $response['response']['cardSecurityCode']['gatewayCode']
            );
        }
    }
}

