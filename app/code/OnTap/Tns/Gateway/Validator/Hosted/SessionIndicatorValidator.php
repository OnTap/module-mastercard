<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Validator\Hosted;

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
            \OnTap\Tns\Observer\Hosted\DataAssignObserver::RESULT_INDICATOR
        );

        $successIndicator = $payment->getAdditionalInformation(
            \OnTap\Tns\Gateway\Response\Hosted\SessionHandler::SUCCESS_INDICATOR
        );

        if ($successIndicator !== $resultIndicator) {
            return $this->createResult(false, ['Hosted Checkout session validation failed.']);
        }

        return $this->createResult(true);
    }
}
