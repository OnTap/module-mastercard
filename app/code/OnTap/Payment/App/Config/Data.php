<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Payment\App\Config;

use Magento\Framework\App\Config;

class Data extends Config\Data
{
    /**
     * @param Config\MetadataProcessor $processor
     * @param array $data
     */
    public function __construct(Config\MetadataProcessor $processor, array $data)
    {
        /** Clone the array to work around a kink in php7 that modifies the argument by reference */
        $this->_data = $processor->process($this->arrayClone($data));
        $this->_source = $data;
    }

    /**
     * Copy array by value
     *
     * @param array $data
     * @return array
     */
    private function arrayClone(array $data)
    {
        $clone = [];
        foreach ($data as $key => $value) {
            $clone[$key]= $value;
        }
        return $clone;
    }
}
