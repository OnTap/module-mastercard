<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Request\Direct;

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

        return [
            'billing' => [
                'address' => [
                    'city' => $billingAddress->getCity(),
                    'company' => $billingAddress->getCompany() != "" ? $billingAddress->getCompany() : null,
                    'country' => $country->getThreeLetterAbbreviation(),
                    'postcodeZip' => $billingAddress->getPostcode(),
                    'stateProvince' => $billingAddress->getRegionCode(),
                    'street' => $billingAddress->getStreetLine1(),
                    'street2' => $billingAddress->getStreetLine2() != "" ? $billingAddress->getStreetLine2() : null
                ]
            ]
        ];
    }
}
