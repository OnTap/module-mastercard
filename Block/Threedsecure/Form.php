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

namespace OnTap\MasterCard\Block\Threedsecure;

use Magento\Framework\Url;
use Magento\Framework\View\Element\Template;
use OnTap\MasterCard\Gateway\Request\ThreeDSecure\CheckDataBuilder;

class Form extends Template
{
    /**
     * @return string
     */
    public function getReturnUrl()
    {
        /** @var Url $urlBuilder */
        $urlBuilder = $this->_urlBuilder;

        return $urlBuilder->setUseSession(true)->getUrl(
            CheckDataBuilder::RESPONSE_URL,
            [
                '_secure' => true,
                '_query' => [
                    CheckDataBuilder::RESPONSE_SID_PARAMETER => $this->_session->getSessionId(),
                ],
            ]
        );
    }
}
