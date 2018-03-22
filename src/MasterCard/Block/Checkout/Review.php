<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Block\Checkout;

class Review extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @param $quote
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $quote->collectTotals();
        $this->quote = $quote;
    }
}
