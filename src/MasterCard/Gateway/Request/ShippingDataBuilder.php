<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
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

        $shippingAddress = $order->getShippingAddress();

        if ($shippingAddress === null) {
            return [];
        }

        $country = $this->countryInfo->getCountryInfo($shippingAddress->getCountryId());

        $regionCode = $shippingAddress->getRegionCode();
        if (empty($regionCode)) {
            $regionCode = null;
        }

        return [
            'shipping' => [
                'address' => [
                    'city' => $shippingAddress->getCity(),
                    'company' => $shippingAddress->getCompany() != "" ? $shippingAddress->getCompany() : null,
                    'country' => $country->getThreeLetterAbbreviation(),
                    'postcodeZip' => $shippingAddress->getPostcode(),
                    'stateProvince' => $regionCode,
                    'street' => $shippingAddress->getStreetLine1(),
                    'street2' => $shippingAddress->getStreetLine2() != "" ? $shippingAddress->getStreetLine2() : null
                ],
                'contact' => [
                    'email' => $shippingAddress->getEmail(),
                    'firstName' => $shippingAddress->getFirstname(),
                    'lastName' => $shippingAddress->getLastname(),
                    'phone' => $shippingAddress->getTelephone()
                ]
            ]
        ];
    }
}
