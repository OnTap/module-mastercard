<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Response;

use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Quote\Api\ShipmentEstimationInterface;

class AddressDataHandler implements HandlerInterface
{
    /**
     * @var \Magento\Quote\Api\Data\AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var CountryInformationAcquirerInterface
     */
    protected $countryInformationAcquirer;

    /**
     * @var ShippingInformationManagementInterface
     */
    protected $shippingInformationManagement;

    /**
     * @var ShippingInformationInterfaceFactory
     */
    protected $shippingInformationFactory;

    /**
     * @var ShipmentEstimationInterface
     */
    protected $shipmentEstimation;

    /**
     * AddressDataHandler constructor.
     * @param ShipmentEstimationInterface $shipmentEstimation
     * @param ShippingInformationInterfaceFactory $shippingInformationFactory
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     * @param CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\Data\AddressInterfaceFactory $addressFactory
     */
    public function __construct(
        ShipmentEstimationInterface $shipmentEstimation,
        ShippingInformationInterfaceFactory $shippingInformationFactory,
        ShippingInformationManagementInterface $shippingInformationManagement,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\Data\AddressInterfaceFactory $addressFactory
    ) {
        $this->addressFactory = $addressFactory;
        $this->cartRepository = $cartRepository;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformationFactory = $shippingInformationFactory;
        $this->shipmentEstimation = $shipmentEstimation;
    }

    /**
     * @param array $data
     * @param array $customer
     * @return \Magento\Quote\Model\Quote\Address
     */
    protected function createAddress($data, $customer)
    {
        /** @var \Magento\Quote\Model\Quote\Address $addressDO */
        $addressDO = $this->addressFactory->create();
        $addressDO->setData([
            'firstname' => $customer['firstName'],
            'lastname' => $customer['lastName'],
            'city' => $data['city'],
            'postcode' => $data['postcodeZip'],
            'street' => $data['street'],
            'telephone' => $customer['mobilePhone'],
            'email' => $customer['email'],
            'region' => $data['stateProvince'],
        ]);

        $country = null;
        foreach ($this->countryInformationAcquirer->getCountriesInfo() as $countryInformation) {
            if ($countryInformation->getThreeLetterAbbreviation() == $data['country']) {
                $country = $countryInformation;
                break;
            }
        }

        $addressDO->setCountryId($country->getId());

        return $addressDO;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = SubjectReader::readPayment($handlingSubject);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $payment->getPayment()->getQuote();

        $customer = $response['customer'];
        $billing = $response['billing']['address'];
        $shipping = $response['shipping']['address'];

        $billingAddressDO = $this->createAddress($billing, $customer);
        $shippingAddressDO = $this->createAddress($shipping, $customer);

        $methods = $this->shipmentEstimation->estimateByExtendedAddress($quote->getId(), $shippingAddressDO);
        $shippingMethod = array_shift($methods);

        if (!$shippingMethod) {
            throw new \Exception(__('Can not find applicable shipping methods.'));
        }

        /** @var \Magento\Checkout\Api\Data\ShippingInformationInterface $shippingInformation */
        $shippingInformation = $this->shippingInformationFactory->create();
        $shippingInformation
            ->setShippingAddress($shippingAddressDO)
            ->setBillingAddress($billingAddressDO)
            ->setShippingMethodCode($shippingMethod->getMethodCode())
            ->setShippingCarrierCode($shippingMethod->getCarrierCode());

        $this->shippingInformationManagement
            ->saveAddressInformation($quote->getId(), $shippingInformation);
    }
}
