<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class TokenCreateValidator extends AbstractValidator
{
    // r.response.gatewayCode
    const BASIC_VERIFICATION_SUCCESSFUL = 'BASIC_VERIFICATION_SUCCESSFUL';
    const NO_VERIFICATION_PERFORMED = 'NO_VERIFICATION_PERFORMED';
    const EXTERNAL_VERIFICATION_SUCCESSFUL = 'EXTERNAL_VERIFICATION_SUCCESSFUL';
    const EXTERNAL_VERIFICATION_DECLINED = 'EXTERNAL_VERIFICATION_DECLINED';
    const EXTERNAL_VERIFICATION_DECLINED_EXPIRED_CARD = 'EXTERNAL_VERIFICATION_DECLINED_EXPIRED_CARD';
    const EXTERNAL_VERIFICATION_DECLINED_INVALID_CSC = 'EXTERNAL_VERIFICATION_DECLINED_INVALID_CSC';
    const EXTERNAL_VERIFICATION_PROCESSING_ERROR = 'EXTERNAL_VERIFICATION_PROCESSING_ERROR';
    const EXTERNAL_VERIFICATION_BLOCKED = 'EXTERNAL_VERIFICATION_BLOCKED';

    // r.result
    const SUCCESS = 'SUCCESS';
    const PENDING = 'PENDING';
    const FAILURE = 'FAILURE';
    const UNKNOWN = 'UNKNOWN';

    // r.status
    const STATUS_VALID = 'VALID';
    const STATUS_INVALID = 'INVALID';

    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        if (isset($response['error'])) {
            return $this->createResult(false, [$response['error']['explanation']]);
        }

        if ($response['status'] == static::STATUS_VALID) {
            return $this->createResult(true);
        }
        return $this->createResult(false, ['Failed to tokenize card']);
    }
}
