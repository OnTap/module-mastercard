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

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Directory\Api\CountryInformationAcquirerInterface;

class BillingDataBuilder implements BuilderInterface
{
    /**
     * @var CountryInformationAcquirerInterface
     */
    protected $countryInfo;

    /**
     * BillingDataBuilder constructor.
     * @param CountryInformationAcquirerInterface $countryInformationAcquirerInterface
     */
    public function __construct(CountryInformationAcquirerInterface $countryInformationAcquirerInterface)
    {
        $this->countryInfo = $countryInformationAcquirerInterface;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();

        if ($billingAddress === null) {
            return [];
        }

        $country = $this->countryInfo->getCountryInfo($billingAddress->getCountryId());

        $regionCode = $billingAddress->getRegionCode();
        if (empty($regionCode)) {
            $regionCode = null;
        }

        return [
            'billing' => [
                'address' => [
                    'city' => $billingAddress->getCity(),
                    'company' => $billingAddress->getCompany() != "" ? $billingAddress->getCompany() : null,
                    'country' => $country->getThreeLetterAbbreviation(),
                    'postcodeZip' => $billingAddress->getPostcode(),
                    'stateProvince' => $regionCode,
                    'street' => $billingAddress->getStreetLine1(),
                    'street2' => $billingAddress->getStreetLine2() != "" ? $billingAddress->getStreetLine2() : null
                ]
            ]
        ];
    }
}
