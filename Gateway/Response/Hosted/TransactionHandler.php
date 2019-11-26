<?php
/**
 * Copyright (c) 2016-2019 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
