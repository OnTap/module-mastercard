<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Block\Threedsecure;

use Magento\Framework\View\Element\Template;
use OnTap\Tns\Gateway\Request\Direct\ThreeDSecureDataBuilder;

class Form extends Template
{
    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->_urlBuilder->getUrl(ThreeDSecureDataBuilder::RESPONSE_URL);
    }
}
