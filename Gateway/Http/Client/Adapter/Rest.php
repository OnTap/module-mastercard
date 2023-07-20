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

namespace OnTap\MasterCard\Gateway\Http\Client\Adapter;

use Magento\Framework\HTTP\Adapter\Curl;
use Laminas\Http\Request;
use Laminas\Uri\Exception\InvalidUriException;
use Laminas\Uri\UriInterface;

class Rest extends Curl
{
    /**
     * Send request to the remote server
     *
     * @param string $method
     * @param UriInterface|string $url
     * @param string $httpVer
     * @param array $headers
     * @param string $body
     * @return string Request as text
     * @throws InvalidUriException
     */
    public function write($method, $url, $httpVer = '1.1', $headers = [], $body = '')
    {
        if ($url instanceof UriInterface) {
            $url = (string)$url;
        }
        $this->_applyConfig();

        // set url to post to
        // @codingStandardsIgnoreStart
        curl_setopt($this->_getResource(), CURLOPT_URL, $url);
        curl_setopt($this->_getResource(), CURLOPT_RETURNTRANSFER, true);

        if ($method == Request::POST) {
            curl_setopt($this->_getResource(), CURLOPT_POST, true);
            curl_setopt($this->_getResource(), CURLOPT_POSTFIELDS, $body);
            $headers[] = 'Content-Length: ' . strlen($body);
        } elseif ($method == Request::PUT) {
            curl_setopt($this->_getResource(), CURLOPT_CUSTOMREQUEST, Request::PUT);
            curl_setopt($this->_getResource(), CURLOPT_POSTFIELDS, $body);
            $headers[] = 'Content-Length: ' . strlen($body);
        } elseif ($method == Request::GET) {
            curl_setopt($this->_getResource(), CURLOPT_HTTPGET, true);
        }

        if (is_array($headers)) {
            curl_setopt($this->_getResource(), CURLOPT_HTTPHEADER, $headers);
        }

        /**
         * @internal Curl options setter have to be re-factored
         */
        $header = isset($this->_config['header']) ? $this->_config['header'] : true;
        curl_setopt($this->_getResource(), CURLOPT_HEADER, $header);
        curl_setopt($this->_getResource(), CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->_getResource(), CURLOPT_SSL_VERIFYHOST, 2);
        // @codingStandardsIgnoreStop

        return $body;
    }
}
