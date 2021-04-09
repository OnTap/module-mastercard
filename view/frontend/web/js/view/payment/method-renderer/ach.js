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
        'uiLayout',
        'mage/translate'
    ],
    function (Component, Layout, $t) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/ach',
                active: false,
                adapter: null,
                adapterLoaded: false,
                buttonTitle: null,
                buttonTitleEnabled: $t('Place Order'),
                buttonTitleDisabled: $t('Please wait...'),
                imports: {
                    onActiveChange: 'active'
                }
            },

            setAdapter: function (adapter) {
                console.log('set adapter %o', adapter)
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
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'active',
                        'adapter',
                        'adapterLoaded',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'buttonTitle'
                    ]);

                this.buttonTitle(this.buttonTitleDisabled);

                this.isPlaceOrderActionAllowed.subscribe(function (allowed) {
                    if (allowed === true && this.isActive()) {
                        this.buttonTitle(this.buttonTitleEnabled);
                    }
                }, this);

                this.adapterLoaded.subscribe(function (loaded) {
                    if (loaded === true && this.isActive()) {
                        this.buttonTitle(this.buttonTitleEnabled);
                    }
                }.bind(this));

                return this;
            },

            onActiveChange: function (isActive) {
                if (isActive && !this.adapterLoaded()) {
                    this.loadAdapter();
                }
            },

            isActive: function () {
                var active = this.getCode() === this.isChecked();
                this.active(active);
                return active;
            },

            isCheckoutDisabled: function () {
                return !this.adapterLoaded() || !this.isPlaceOrderActionAllowed();
            },

            loadAdapter: function () {
                if (this.adapter()) {
                    this.adapter().load(this.paymentAdapterLoaded.bind(this));
                } else {
                    this.adapter.subscribe(function (adapter) {
                        adapter.load(this.paymentAdapterLoaded.bind(this));
                    }.bind(this));
                }
            },

            paymentAdapterLoaded: function () {
                this.isPlaceOrderActionAllowed(false);
                this.buttonTitle(this.buttonTitleDisabled);

                console.log('We have loaded adapter');
                this.adapterLoaded(true);
                this.isPlaceOrderActionAllowed(true);
            }
       });
    }
);
