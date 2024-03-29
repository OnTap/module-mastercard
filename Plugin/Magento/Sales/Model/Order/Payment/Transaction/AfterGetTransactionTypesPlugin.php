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

namespace OnTap\MasterCard\Plugin\Magento\Sales\Model\Order\Payment\Transaction;

use Magento\Sales\Model\Order\Payment\Transaction;
use OnTap\MasterCard\Api\Data\TransactionInterface;

class AfterGetTransactionTypesPlugin
{
    /**
     * @param Transaction $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetTransactionTypes(Transaction $subject, $result)
    {
        return array_merge($result, [
            TransactionInterface::TYPE_VERIFY => __('Verify'),
        ]);
    }
}
