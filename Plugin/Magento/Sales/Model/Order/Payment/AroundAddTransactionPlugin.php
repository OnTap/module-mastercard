<?php
/**
 * Copyright (c) 2016-2022 Mastercard
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

namespace OnTap\MasterCard\Plugin\Magento\Sales\Model\Order\Payment;

use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use OnTap\MasterCard\Api\Data\TransactionInterface;
use OnTap\MasterCard\Api\VerifyPaymentFlagInterface;

class AroundAddTransactionPlugin
{
    /**
     * @var VerifyPaymentFlagInterface
     */
    private $verifyPaymentFlag;

    /**
     * @param VerifyPaymentFlagInterface $verifyPaymentFlag
     */
    public function __construct(VerifyPaymentFlagInterface $verifyPaymentFlag)
    {
        $this->verifyPaymentFlag = $verifyPaymentFlag;
    }

    /**
     * @param Payment $subject
     * @param callable $proceed
     * @param string $type
     * @param AbstractModel $salesDocument
     * @param bool $failSafe
     *
     * @return null|Transaction
     */
    public function aroundAddTransaction(
        $subject,
        callable $proceed,
        $type,
        $salesDocument = null,
        $failSafe = false
    ) {
        $method = $subject->getMethod();
        if (!$this->verifyPaymentFlag->isVerifyPayment($method)) {
            return $proceed($type, $salesDocument, $failSafe);
        }

        return $proceed(TransactionInterface::TYPE_VERIFY, $salesDocument, $failSafe);
    }
}
