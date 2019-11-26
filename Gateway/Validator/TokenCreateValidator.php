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
