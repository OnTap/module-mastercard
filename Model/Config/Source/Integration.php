<?php
/**
 * Copyright (c) 2016-2021 Mastercard
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

declare(strict_types=1);

namespace OnTap\MasterCard\Model\Config\Source;

class Integration implements \Magento\Framework\Data\OptionSourceInterface
{
    const HOSTED_CHECKOUT = 'hosted_checkout';
    const HOSTED_SESSION = 'hosted_session';

    const INTEGRATION_MODES = [
        self::HOSTED_CHECKOUT,
        self::HOSTED_SESSION
    ];

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('Hosted Checkout'),
                'value' => self::HOSTED_CHECKOUT
            ],
            [
                'label' => __('Hosted Session'),
                'value' => self::HOSTED_SESSION
            ],
        ];
    }
}
