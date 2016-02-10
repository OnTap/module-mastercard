<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Block\Threedsecure;

use Magento\Framework\View\Element\Template;
use OnTap\Tns\Gateway\Request\ThreeDSecure\CheckDataBuilder;

class Form extends Template
{
    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->_urlBuilder->getUrl(CheckDataBuilder::RESPONSE_URL);
    }
}
