<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Response\Hosted;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Helper\ContextHelper;
use OnTap\MasterCard\Gateway\Response\PaymentHandler;

class TransactionHandler implements HandlerInterface
{
    const TYPE_AUTHORIZE = 'AUTHORIZATION';
    const TYPE_PAYMENT = 'PAYMENT';
    const TYPE_VERIFICATION = 'VERIFICATION';

    /**
     * @param array $txn
     * @param Payment $payment
     * @return void
     */
    protected function handleTransaction(array $txn, Payment $payment)
    {
        $type = $txn['transaction']['type'];

        if ($type === static::TYPE_VERIFICATION) {
            // noop
            return;
        }
        if ($type === static::TYPE_AUTHORIZE) {
            $this->authorize($txn, $payment);
            return;
        }
        if ($type === static::TYPE_PAYMENT) {
            $this->payment($txn, $payment);
            return;
        }

        throw new \InvalidArgumentException(sprintf("Type '%s' is not a valid transaction type for HC", $type));
    }

    /**
     * @param array $txn
     * @param Payment $payment
     * @return void
     */
    protected function authorize(array $txn, Payment $payment)
    {
        $payment->setTransactionId($txn['transaction']['id']);
        $payment->setIsTransactionClosed(false);

        PaymentHandler::importPaymentResponse($payment, $txn);

        // @todo: Actually add this transaction into list
    }

    /**
     * @param array $txn
     * @param Payment $payment
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function payment(array $txn, Payment $payment)
    {
        $payment->setTransactionId($txn['transaction']['id']);
        $payment->setIsTransactionClosed(true);

        PaymentHandler::importPaymentResponse($payment, $txn);
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $transactions = $response['transaction'];

        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        // @todo: Support for subsequent transactions?
        foreach ($transactions as $transaction) {
            $this->handleTransaction($transaction, $payment);
        }
    }
}
