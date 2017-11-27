<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Block\Masterpass;

use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Address\Rate;

class Review extends Template
{
    /**
     * Currently selected shipping rate
     *
     * @var Rate
     */
    protected $_currentShippingRate = null;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote = null;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $addressConfig;

    /**
     * @var \Magento\Quote\Model\Quote\Address
     */
    protected $_address;

    /**
     * Review constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->addressConfig = $addressConfig;
    }

    protected function _beforeToHtml()
    {
//        $methodInstance = $this->getQuote()->getPayment()->getMethodInstance();
//        $this->setPaymentMethodTitle($methodInstance->getTitle());
        $this->setPaymentMethodTitle('some test payment method');

        return parent::_beforeToHtml();
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    /**
     * Get HTML output for specified address
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return string
     */
    public function renderAddress($address)
    {
        /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
        $addressData = \Magento\Framework\Convert\ConvertArray::toFlatArray($address->getData());
        return $renderer->renderArray($addressData);
    }

    /**
     * Return quote shipping address
     *
     * @return false|\Magento\Quote\Model\Quote\Address
     */
    public function getShippingAddress()
    {
        if ($this->getQuote()->getIsVirtual()) {
            return false;
        }
        return $this->getQuote()->getShippingAddress();
    }
}
