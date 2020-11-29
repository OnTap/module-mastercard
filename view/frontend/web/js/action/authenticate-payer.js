/*
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
define(
    [
        'jquery',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (
        $,
        errorProcessor
    ) {
        'use strict';

        return {
            apiUrl: '/tns/threedsecureV2/authenticatePayer',
            /**
             *
             * @param {Object} payload
             * @param {Object} payload.browserDetails
             * @param {Boolean} payload.browserDetails.javaEnabled
             * @param {String} payload.browserDetails.language
             * @param {Number} payload.browserDetails.screenHeight
             * @param {Number} payload.browserDetails.screenWidth
             * @param {Number} payload.browserDetails.timeZone
             * @param {Number} payload.browserDetails.colorDepth
             * @param {String} payload.browserDetails.acceptHeaders
             * @param {String} payload.browserDetails['3DSecureChallengeWindowSize']
             *
             * @param messageContainer
             * @returns {*}
             */
            execute: function (payload, messageContainer) {

                return $.post(this.apiUrl, payload)
                    .fail(
                        function (response) {
                            errorProcessor.process(response, messageContainer);
                        }
                    );
            }
        }
    }
);
