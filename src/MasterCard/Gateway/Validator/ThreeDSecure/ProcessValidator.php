<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Validator\ThreeDSecure;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class ProcessValidator extends AbstractValidator
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

        if (!isset($response['3DSecure']['summaryStatus'])) {
            return $this->createResult(false, [__('3D-Secure verification error.')]);
        }

        switch ($response['3DSecure']['summaryStatus']) {
            case 'AUTHENTICATION_SUCCESSFUL':
            case 'CARD_DOES_NOT_SUPPORT_3DS':
                $result = $this->createResult(true);
                break;

            default:
            case 'AUTHENTICATION_NOT_AVAILABLE':
            case 'AUTHENTICATION_FAILED':
            case 'AUTHENTICATION_ATTEMPTED':
                $result = $this->createResult(false, [__('Transaction declined by 3D-Secure validation.')]);
                break;
        }

        return $result;
    }
}
