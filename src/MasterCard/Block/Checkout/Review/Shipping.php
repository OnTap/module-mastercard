<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Block\Checkout\Review;

class Shipping extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @var \Magento\Checkout\Block\Checkout\AttributeMerger
     */
    protected $merger;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $countryCollection;

    /**
     * @var \Magento\Directory\Model\TopDestinationCountries
     */
    protected $topDestinationCountries;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $regionCollection;

    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $objectCopyService;

    /**
     * Shipping constructor.
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $merger
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection,
        \Magento\Directory\Model\TopDestinationCountries $topDestinationCountries = null,
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->merger = $merger;
        $this->countryCollection = $countryCollection;
        $this->topDestinationCountries = $topDestinationCountries;
        $this->regionCollection = $regionCollection;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * @todo Create a before interceptor for this
     * @return string
     */
    public function getJsLayout()
    {
        $config = [];
        if (isset($this->jsLayout['components']['review-shipping-address']['config'])) {
            $config = $this->jsLayout['components']['review-shipping-address']['config'];
        }
        $this->jsLayout['components']['review-shipping-address']['config'] = array_merge($config, [
            'shippingFromWallet' => $this->getWalletShippingAddress()
        ]);

        $elements = [
            'country_id' => [
                'visible' => true,
                'formElement' => 'select',
                'label' => __('Country'),
                'options' => [],
                'value' => null
            ],
            'region_id' => [
                'visible' => true,
                'formElement' => 'select',
                'label' => __('State/Province'),
                'options' => [],
                'value' => null
            ],
        ];

        if (!isset($this->jsLayout['components']['checkoutProvider']['dictionaries'])) {
            $this->jsLayout['components']['checkoutProvider']['dictionaries'] = [
                'country_id' => $this->countryCollection->loadByStore()->setForegroundCountries(
                    $this->topDestinationCountries->getTopDestinations()
                )->toOptionArray(),
                'region_id' => $this->regionCollection->addAllowedCountriesFilter()->toOptionArray(),
            ];
        }

        if (isset($this->jsLayout['components']['review-shipping-address']['children']['shipping-address-popup']['children']
            ['shipping-address-fieldset']['children'])
        ) {
            $fieldSetPointer = &$this->jsLayout['components']['review-shipping-address']['children']['shipping-address-popup']
            ['children']['shipping-address-fieldset']['children'];
            $fieldSetPointer = $this->merger->merge($elements, 'checkoutProvider', 'shippingAddress', $fieldSetPointer);
            //$fieldSetPointer['region_id']['config']['skipValidation'] = true;
        }

        return parent::getJsLayout();
    }

    /**
     * @return array
     */
    protected function getWalletShippingAddress()
    {
        $address = $this->_checkoutSession->getQuote()->getShippingAddress();
        $customerAddressData = $this->objectCopyService->getDataFromFieldset(
            'sales_convert_quote_address',
            'to_customer_address',
            $address
        );

        $customerAddressData['company'] = '';

        return $customerAddressData;
    }
}
