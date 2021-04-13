<?php

namespace OnTap\MasterCard\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class AchPaymentDetails implements HandlerInterface
{
    const ACCOUNT_TYPE = 'accountType';
    const ACCOUNT_HOLDER = 'bankAccountHolder';
    const ACCOUNT_NUMBER = 'bankAccountNumber';
    const ROUTING_NUMBER = 'routingNumber';
    const SEC_CODE = 'secCode';

    /**
     * @var string[]
     */
    protected $additionalAccountInfo = [
        self::ACCOUNT_TYPE,
        self::ACCOUNT_HOLDER,
        self::ACCOUNT_NUMBER,
        self::ROUTING_NUMBER,
        self::SEC_CODE
    ];

    /**
     * @inheridoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var PaymentDataObject $payment */
        $payment = SubjectReader::readPayment($handlingSubject);

        /** @var OrderPaymentInterface $payment */
        $payment = $payment->getPayment();

        $payment->setLastTransId($response['transaction']['id']);

        // Set transaction as pending because ACH does not capture it immediately
        $payment->setIsTransactionPending(true);

        $sourceOfFunds = $response['sourceOfFunds']['provided']['ach'];
        $additionalInfo = [];
        foreach ($this->additionalAccountInfo as $item) {
            if (!isset($sourceOfFunds[$item])) {
                continue;
            }
            $additionalInfo[$item] = $sourceOfFunds[$item];
        }

        $additionalInfo['gateway_code'] = $response['response']['gatewayCode'];
        $additionalInfo['txn_result'] = $response['result'];

        $payment->setAdditionalInformation($additionalInfo);
    }
}
