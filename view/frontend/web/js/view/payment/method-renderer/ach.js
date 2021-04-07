/*
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
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'uiLayout'
    ],
    function (Component, Layout) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/ach',
            },

            /**
             * Return config for current method
             * including config for integration mode
             * @returns {*}
             */
            getConfig: function () {
                return window.checkoutConfig.payment[this.item.method];
            },

            /**
             * @inheritDoc
             * @returns {*}
             */
            initialize: function () {
                this._super();
                var config = this.getConfig();
                Layout([
                    {
                        parent: this.name,
                        name: this.name + '.' + config['renderer'],
                        displayArea: 'payment-ui',
                        item: this.item,
                        component: 'OnTap_MasterCard/js/view/payment/method-renderer/ach/' + config['renderer'],
                        config: config
                    }
                ]);
                return this;
            }
       });
    }
);
