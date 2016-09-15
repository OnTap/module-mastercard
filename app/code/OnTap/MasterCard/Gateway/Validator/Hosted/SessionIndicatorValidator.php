<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
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
