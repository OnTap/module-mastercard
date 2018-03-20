<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Helper\SubjectReader;

class SessionValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $errors = [];

        if (!isset($response['session'])) {
            $errors[] = __('Invalid session response.');
        }

        if (!isset($response['billing']) || !isset($response['shipping'])) {
            $errors[] = __('Session information does not contain billing/shipping address.');
        }

        if (!empty($errors)) {
            return $this->createResult(false, $errors);
        }

        return $this->createResult(true);
    }
}
