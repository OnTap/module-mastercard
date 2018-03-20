<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Block\Checkout;

use Magento\Framework\View\Element\Template;

class Review extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $addressConfig;

    /**
     * Review constructor.
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Address\Config $addressConfig,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->addressConfig = $addressConfig;
    }

    /**
     * @param $quote
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $quote->collectTotals();
        $this->quote = $quote;
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

    public function getBillingAddressHtml()
    {
        return $this->renderAddress($this->quote->getBillingAddress());
    }

    public function getShippingAddress()
    {
        return $this->renderAddress($this->quote->getShippingAddress());
    }
}
