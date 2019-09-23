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

namespace OnTap\MasterCard\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class OrderValidator extends AbstractValidator
{
    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $errors = [];

        $payment = SubjectReader::readPayment($validationSubject);
        $response = SubjectReader::readResponse($validationSubject);

        //order.totalAuthorizedAmount
        //order.totalCapturedAmount
        //order.totalRefundedAmount
        //if (number_format((float)$amount, 2) !== number_format($response['order']['amount'], 2)) {
        //    $errors[] = "Amount mismatch";
        //}

        if ($payment->getOrder()->getOrderIncrementId() !== $response['order']['id']) {
            $errors[] = __("OrderID mismatch");
        }

        if ($payment->getOrder()->getCurrencyCode() !== $response['order']['currency']) {
            $errors[] = __("Currency mismatch");
        }

        if (count($errors) > 0) {
            return $this->createResult(false, $errors);
        }

        return $this->createResult(true);
    }
}
