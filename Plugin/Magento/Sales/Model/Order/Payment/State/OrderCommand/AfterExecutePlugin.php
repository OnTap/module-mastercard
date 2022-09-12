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

namespace OnTap\MasterCard\Plugin\Magento\Sales\Model\Order\Payment\State\OrderCommand;

use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\State\OrderCommand;
use OnTap\MasterCard\Api\Data\OrderInterface;
use OnTap\MasterCard\Api\VerifyPaymentFlagInterface;

class AfterExecutePlugin
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
     * @param OrderCommand $subject
     * @param Phrase $result
     * @param OrderPaymentInterface $payment
     * @param float $amount
     * @param Order $order
     *
     * @return Phrase
     */
    public function afterExecute(
        $subject,
        $result,
        $payment,
        $amount,
        $order
    ) {
        $method = $payment->getMethod();
        if (!$this->verifyPaymentFlag->isVerifyPayment($method)) {
            return $result;
        }

        $order->setStatus(OrderInterface::STATUS_PAYMENT_VERIFIED);

        $message = 'Ordered amount of %1 is verified by the payment gateway.';
        return __($message, $order->getBaseCurrency()->formatTxt($amount));
    }
}
