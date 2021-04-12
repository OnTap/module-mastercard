<?php
/**
 * Copyright (c) 2016-2021 Mastercard
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

use Magento\Payment\Gateway\Helper\SubjectReader;

class AchSuccessful extends ResponseValidator
{
    /**
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        if (!isset($response['result'])) {
            return $this->createResult(false, [__("Response does not contain a body.")]);
        }

        if (isset($response['error']) && $response['error']['validationType'] === 'MISSING') {
            return $this->createResult(false, [$response['error']['explanation']]);
        }

        return parent::validate($validationSubject);
    }
}
