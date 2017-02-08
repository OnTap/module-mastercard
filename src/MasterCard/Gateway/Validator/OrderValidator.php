<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
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
