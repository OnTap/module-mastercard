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

declare(strict_types=1);

namespace OnTap\MasterCard\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\ConfigInterface as MagentoConfigInterface;

class Is3DS2EnabledHandler implements ValueHandlerInterface
{
    /**
     * @var MagentoConfigInterface
     */
    private $configInterface;

    /**
     * @param MagentoConfigInterface $configInterface
     */
    public function __construct(
        ConfigInterface $configInterface
    ) {
        $this->configInterface = $configInterface;
    }

    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return bool
     */
    public function handle(array $subject, $storeId = null)
    {
        return (int)$this->configInterface->getValue('three_d_secure', $storeId)
        && (int)$this->configInterface->getValue('three_d_secure_version', $storeId) === 2;
    }
}
