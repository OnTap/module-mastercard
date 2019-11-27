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

namespace OnTap\MasterCard\Gateway\Request\ThreeDSecure;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use OnTap\MasterCard\Gateway\Response\ThreeDSecure\CheckHandler;

class ResultsDataBuilder implements BuilderInterface
{
    const ENROLLED = 'ENROLLED';
    const NOT_ENROLLED = 'NOT_ENROLLED';
    const ENROLLMENT_STATUS_UNDETERMINED = 'ENROLLMENT_STATUS_UNDETERMINED';

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * ResultsDataBuilder constructor.
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param PaymentDataObjectInterface $paymentDO
     * @return string
     */
    protected function getEnrollmentStatus(PaymentDataObjectInterface $paymentDO)
    {
        $tdsCheck = $paymentDO->getPayment()->getAdditionalInformation(CheckHandler::THREEDSECURE_CHECK);

        switch ($tdsCheck['veResEnrolled']) {
            case 'Y':
                $status = static::ENROLLED;
                break;

            case 'N':
                $status = static::NOT_ENROLLED;
                break;

            default:
                $status = static::ENROLLMENT_STATUS_UNDETERMINED;
                break;
        }

        return $status;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if ($this->config->getValue('three_d_secure') !== '1') {
            return [];
        }

        $paymentDO = SubjectReader::readPayment($buildSubject);
        return [
            '3DSecureId' => $paymentDO->getPayment()->getAdditionalInformation('3DSecureId'),
        ];
    }
}
