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

declare(strict_types=1);

namespace OnTap\MasterCard\Gateway\Request\Authentication;

use InvalidArgumentException;
use Magento\Payment\Gateway\Request\BuilderInterface;

class DeviceBuilder implements BuilderInterface
{
    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        return [
            'device' => [
                'browser' => $this->readBrowser($buildSubject),
                'browserDetails' => $this->readBrowserDetails($buildSubject),
                'ipAddress' => $this->readRemoteIp($buildSubject),
            ]
        ];
    }

    /**
     * Reads payment from subject
     *
     * @param array $subject
     * @return array
     */
    private function readBrowserDetails(array $subject)
    {
        if (!isset($subject['browserDetails'])) {
            throw new InvalidArgumentException('Browser detail data array should be provided');
        }

        return $subject['browserDetails'];
    }

    /**
     * Reads payment from subject
     *
     * @param array $subject
     * @return string
     */
    private function readRemoteIp(array $subject)
    {
        if (!isset($subject['remote_ip'])) {
            throw new InvalidArgumentException('Remote Ip should be provided');
        }

        return $subject['remote_ip'];
    }

    /**
     * Reads browser from subject
     *
     * @param array $subject
     * @return string
     */
    private function readBrowser(array $subject)
    {
        if (!isset($subject['browser'])) {
            throw new InvalidArgumentException('Browser should be provided');
        }

        return $subject['browser'];
    }
}
