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
     * @param string $data
     * @param string $field
     * @return string|null
     */
    public static function safeValue($data, $field)
    {
        return isset($data[$field]) ? $data[$field] : null;
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
            if (isset($cardDetails['fundingMethod'])) {
                $payment->setAdditionalInformation('fundingMethod', static::safeValue($cardDetails, 'fundingMethod'));
            }
            if (isset($cardDetails['issuer'])) {
                $payment->setAdditionalInformation('issuer', static::safeValue($cardDetails, 'issuer'));
            }
            if (isset($cardDetails['nameOnCard'])) {
                $payment->setAdditionalInformation('nameOnCard', static::safeValue($cardDetails, 'nameOnCard'));
            }
        }

        if (isset($response['response']['cardSecurityCode'])) {
            $payment->setAdditionalInformation(
                'cvv_validation',
                $response['response']['cardSecurityCode']['gatewayCode']
            );
        }
    }
}
