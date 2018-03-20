<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Block\Cart\AmexButton;

use OnTap\MasterCard\Block\Cart\AmexButton;

class Direct extends AmexButton
{
    /**
     * @return string
     */
    public function getJsConfig()
    {
        return \Zend_Json_Encoder::encode($this->method->getJsConfig());
    }
}
