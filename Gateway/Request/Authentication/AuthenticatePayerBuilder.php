<?php
/**
 * Copyright (c) 2016-2020 Mastercard
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

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use OnTap\MasterCard\Gateway\Request\ThreeDSecure\CheckDataBuilder;

class AuthenticatePayerBuilder implements BuilderInterface
{
    public const RESPONSE_URL = 'tns/threedsecureV2/response';

    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * AuthenticationPayerBuilder constructor.
     * @param UrlInterface $url
     * @param SessionManagerInterface $session
     */
    public function __construct(
        UrlInterface $url,
        SessionManagerInterface $session
    ) {
        $this->url = $url;
        $this->session = $session;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        $url = $this->url->setUseSession(true)->getUrl(
            self::RESPONSE_URL,
            [
                '_secure' => true,
                '_query' => [
                    CheckDataBuilder::RESPONSE_SID_PARAMETER => $this->session->getSessionId(),
                ],
            ]
        );

        return [
            'authentication' => [
                'redirectResponseUrl' => $url
            ]
        ];
    }
}
