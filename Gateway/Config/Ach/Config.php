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

namespace OnTap\MasterCard\Gateway\Config\Ach;

use Magento\Framework\Exception\NoSuchEntityException;
use OnTap\MasterCard\Api\MethodInterface;
use OnTap\MasterCard\Gateway\Config\ConfigInterface;

class Config extends \OnTap\MasterCard\Gateway\Config\Config implements ConfigInterface
{
    /**
     * @var string
     */
    protected $method = 'mpgs_ach';

    /**
     * @return bool
     */
    public function isVaultEnabled(): bool
    {
        return false;
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isOrderTokenizationEnabled(): bool
    {
        $storeId = $this->storeManager->getStore()->getId();
        $paymentAction = $this->getValue('mapped_payment_action', $storeId);
        if ($paymentAction === MethodInterface::MAPPED_ACTION_ORDER_VERIFY) {
            return true;
        }

        return (bool)$this->getValue('add_token_to_order');
    }
}
