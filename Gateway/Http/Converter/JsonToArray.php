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

namespace OnTap\MasterCard\Gateway\Http\Converter;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;

class JsonToArray implements ConverterInterface
{
    /**
     * @var Json
     */
    private $json;

    /**
     * JsonToArray constructor.
     * @param Json $json
     */
    public function __construct(
        Json $json
    ) {
        $this->json = $json;
    }

    /**
     * Converts gateway response to ENV structure
     *
     * @param mixed $response
     * @return array
     * @throws ConverterException
     * @throws \InvalidArgumentException
     */
    public function convert($response)
    {
        if (!is_string($response) || empty($response)) {
            throw new ConverterException(__('Wrong response type'));
        }

        return $this->json->unserialize($response);
    }
}
