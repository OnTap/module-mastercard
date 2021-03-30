<?php
/**
 * Copyright (c) 2016-2020 Mastercard
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

namespace OnTap\MasterCard\Gateway\Validator\Authentication;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class AuthenticatePayerValidator extends AbstractValidator
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * InitiateAuthValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ArrayManager $arrayManager
    ) {
        parent::__construct($resultFactory);
        $this->arrayManager = $arrayManager;
    }

    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        $error = $this->arrayManager->get('error', $response);
        $result = $this->arrayManager->get('result', $response);
        $gatewayRecommendation = $this->arrayManager->get('response/gatewayRecommendation', $response);
        $transactionId = $this->arrayManager->get('transaction/id', $response);

        if (isset($error)) {
            return $this->createResult(false, ['Error']); // TODO map errors on correct errors for customers
        }

        $version = $this->arrayManager->get('authentication/version', $response);

        if ($version === 'NONE' && $transactionId && $gatewayRecommendation === 'PROCEED') {
            return $this->createResult(true);
        }

        if ($version !== '3DS1' && $version !== '3DS2') {
            return $this->createResult(false, [
                'Unsupported version of 3DS'
            ]);
        }

        $statuses = ['SUCCESS', 'PROCEED', 'PENDING'];
        if (!in_array($result, $statuses) || $gatewayRecommendation !== 'PROCEED') {
            return $this->createResult(false, ['Transaction declined']);
        }

        return $this->createResult(true);
    }
}
