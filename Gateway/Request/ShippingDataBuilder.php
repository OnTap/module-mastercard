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

class ShippingDataBuilder implements BuilderInterface
{
    /**
     * @var CountryInformationAcquirerInterface
     */
    protected $countryInfo;

    /**
     * ShippingDataBuilder constructor.
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
        $payment = $paymentDO->getPayment();
        $quote = $payment->getQuote();

        if ($quote && $quote->isVirtual()) {
            return [];
        }

        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress === null) {
            return [];
        }

        $country = $this->countryInfo->getCountryInfo($shippingAddress->getCountryId());

        $regionCode = $shippingAddress->getRegionCode();
        if (empty($regionCode)) {
            $regionCode = null;
        }

        $shippingAddressData = [
            'city' => $shippingAddress->getCity(),
            'company' => $shippingAddress->getCompany() != "" ? $shippingAddress->getCompany() : null,
            'country' => $country->getThreeLetterAbbreviation(),
            'postcodeZip' => $shippingAddress->getPostcode(),
            'stateProvince' => $regionCode,
            'street' => $shippingAddress->getStreetLine1(),
            'street2' => $shippingAddress->getStreetLine2() != "" ? $shippingAddress->getStreetLine2() : null
        ];

        $contactData = [
            'email' => $shippingAddress->getEmail(),
            'firstName' => $shippingAddress->getFirstname(),
            'lastName' => $shippingAddress->getLastname(),
        ];

        if ($shippingAddress->getTelephone()) {
            $contactData['phone'] = $shippingAddress->getTelephone();
        }

        return [
            'shipping' => [
                'address' => $shippingAddressData,
                'contact' => $contactData
            ]
        ];
    }
}
