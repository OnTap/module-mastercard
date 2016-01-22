<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Validator\Direct;

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
            $msg = $response['error']['cause'].': '.$response['error']['explanation'] . ' ('.$response['error']['supportCode'].')';
            return $this->createResult(false, [$msg, ]);
        }

        return $this->createResult(true);
    }
}
