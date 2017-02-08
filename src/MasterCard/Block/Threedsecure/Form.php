<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Block\Threedsecure;

use Magento\Framework\View\Element\Template;
use OnTap\MasterCard\Gateway\Request\ThreeDSecure\CheckDataBuilder;

class Form extends Template
{
    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->_urlBuilder->getUrl(CheckDataBuilder::RESPONSE_URL, ['_secure' => true]);
    }
}
