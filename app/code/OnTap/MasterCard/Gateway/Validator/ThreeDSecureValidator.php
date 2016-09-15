<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class ThreeDSecureValidator extends AbstractValidator
{
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
            $msg = sprintf(
                '%s: %s %s',
                $response['error']['cause'],
                $response['error']['explanation'],
                isset($response['error']['supportCode']) ? $response['error']['supportCode'] : ''
            );
            return $this->createResult(false, [$msg, ]);
        }

        if (!isset($response['3DSecure'])) {
            return $this->createResult(false, ['No 3Ds data was provided.', ]);
        }

        return $this->createResult(true);
    }
}
