<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class WalletValidator extends AbstractValidator
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
            return $this->createResult(false, [__("Unable to open the wallet, please try again later.")]);
        }

        if (!isset($response['session']) || !isset($response['session']['updateStatus'])) {
            return $this->createResult(false, [__("Response does not contain valid session data.")]);
        }

        if ($response['session']['updateStatus'] !== 'SUCCESS') {
            return $this->createResult(false, [__("Invalid session data")]);
        }

        if (!isset($response['wallet'])) {
            return $this->createResult(false, [__("Response does not contain wallet data.")]);
        }

        return $this->createResult(true);
    }
}
