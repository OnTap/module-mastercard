<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;
use OnTap\MasterCard\Gateway\Config\Config;

class Gateway implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::API_EUROPE,
                'label' => __('Europe')
            ],
            [
                'value' => Config::API_ASIA,
                'label' => __('Asia Pacific')
            ],
            [
                'value' => Config::API_AMERICA,
                'label' => __('North America')
            ],
            [
                'value' => Config::API_UAT,
                'label' => __('UAT')
            ],
            [
                'value' => Config::API_OTHER,
                'label' => __('Other')
            ],
        ];
    }
}
