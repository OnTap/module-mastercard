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

namespace OnTap\MasterCard\Gateway\Validator\Hosted;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class SessionIndicatorValidator extends AbstractValidator
{
    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $paymentDO = SubjectReader::readPayment($validationSubject);

        $payment = $paymentDO->getPayment();

        // @todo: Move RESULT_INDICATOR out of DataAssignObserver
        $resultIndicator = $payment->getAdditionalInformation(
            \OnTap\MasterCard\Observer\Hosted\DataAssignObserver::RESULT_INDICATOR
        );

        $successIndicator = $payment->getAdditionalInformation(
            \OnTap\MasterCard\Gateway\Response\Hosted\SessionHandler::SUCCESS_INDICATOR
        );

        if ($successIndicator !== $resultIndicator) {
            return $this->createResult(false, ['Hosted Checkout session validation failed.']);
        }

        return $this->createResult(true);
    }
}
