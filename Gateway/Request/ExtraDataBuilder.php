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

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\ConfigInterface;

class ExtraDataBuilder implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var string
     */
    protected $field;

    /**
     * ExtraDataBuilder constructor.
     * @param ConfigInterface $config
     * @param string $field
     */
    public function __construct(
        ConfigInterface $config,
        Json $json,
        $field = ''
    ) {
        $this->config = $config;
        $this->field = $field;
        $this->json = $json;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $value  = $this->config->getValue($this->field);
        return $this->json->unserialize($value);
    }
}
