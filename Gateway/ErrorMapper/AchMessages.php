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

namespace OnTap\MasterCard\Gateway\ErrorMapper;

use Magento\Framework\Config\DataInterface;

class AchMessages implements DataInterface
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function merge(array $config)
    {
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($key, $default = null)
    {
        return [
            // code => message
            'sourceOfFunds.provided.ach.bankAccountNumber' => 'Missing ACH bank account number',
            'sourceOfFunds.provided.ach.bankAccountHolder' => 'Missing parameter Bank Account Holder',
            'sourceOfFunds.provided.ach.routingNumber' => 'Missing parameter Routing Number'
        ][$key];
    }
}
