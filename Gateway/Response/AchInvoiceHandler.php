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

namespace OnTap\MasterCard\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use OnTap\MasterCard\Model\Operation\InvoiceOperation;
use Magento\Payment\Gateway\Helper\SubjectReader;

class AchInvoiceHandler implements HandlerInterface
{
    /**
     * @var InvoiceOperation
     */
    protected $invoiceOperation;

    /**
     * @param InvoiceOperation $invoiceOperation
     */
    public function __construct(
        InvoiceOperation $invoiceOperation
    ) {
        $this->invoiceOperation = $invoiceOperation;
    }

    /**
     * @inheridoc
     * @todo: multistep_ach
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();

        $this->invoiceOperation->execute($payment);
        $payment->getOrder()->save();
    }
}
