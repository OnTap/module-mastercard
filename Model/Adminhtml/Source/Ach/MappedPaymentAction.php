<?php
/**
 * Copyright (c) 2016-2022 Mastercard
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

namespace OnTap\MasterCard\Model\Adminhtml\Source\Ach;

use OnTap\MasterCard\Api\MethodInterface;
use OnTap\MasterCard\Model\Adminhtml\Source\PaymentAction as BasicPaymentAction;

class MappedPaymentAction extends BasicPaymentAction
{
    /**
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => MethodInterface::MAPPED_ACTION_ORDER_PAY,
                'label' => __('Pay'),
            ],
            [
                'value' => MethodInterface::MAPPED_ACTION_ORDER_VERIFY,
                'label' => __('Verify and Add Token to Order'),
            ],
        ];
    }
}
