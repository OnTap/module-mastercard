<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Block\Checkout\Review;

class Billing extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $addressConfig;

    /**
     * Billing constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Address\Config $addressConfig,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->addressConfig = $addressConfig;
    }

    /**
     * Get HTML output for specified address
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return string
     */
    protected function renderAddress($address)
    {
        /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
        $addressData = \Magento\Framework\Convert\ConvertArray::toFlatArray($address->getData());
        return $renderer->renderArray($addressData);
    }

    /**
     * @return string
     */
    public function getBillingAddressHtml()
    {
        return $this->renderAddress($this->_checkoutSession->getQuote()->getBillingAddress());
    }

    /**
     * @return string
     */
    public function getBillingAddressJs()
    {
        $address = $this->_checkoutSession->getQuote()->getBillingAddress()->exportCustomerAddress()->__toArray();
        return \Zend_Json_Encoder::encode($address);
    }
}
