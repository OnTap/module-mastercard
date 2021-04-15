<?php
/**
 * Copyright (c) 2016-2021 Mastercard
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
declare(strict_types=1);

namespace OnTap\MasterCard\Model\Operation;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Api\TransactionRepositoryInterface;

class WebhookNotificationOperation
{
    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * WebhookNotificationOperation constructor.
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return OrderPaymentInterface
     * @throws LocalizedException
     */
    public function execute(OrderPaymentInterface $payment): OrderPaymentInterface
    {
        /** @var Payment $payment */
        $invoice = $payment->getOrder()->prepareInvoice();
        $invoice->register();

        $transaction = $this->transactionRepository->getByTransactionType(
            'order',
            $payment->getId()
        );

        $isPending = !(bool) $transaction->getIsClosed();

        if ($isPending) {
            $transaction->setIsClosed(1);
            $this->transactionRepository->save($transaction);

            $invoice->setIsPaid(true);
            $invoice->setTransactionId($payment->getLastTransId());
            $invoice->pay();

            $payment->getOrder()->addRelatedObject($invoice);
            $payment->setCreatedInvoice($invoice);
        }

        return $payment;
    }
}
