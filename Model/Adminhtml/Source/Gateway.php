<?php
/**
 * Copyright (c) 2016-2019 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
