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
        'mage/translate',
        'Magento_Checkout/js/action/set-payment-information',
        'jquery'
    ],
    function (Component, Layout, $t, setPaymentInformationAction, $) {
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
                sessionId: null,
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

                this.adapter().configure(function() {
                    this.adapterLoaded(true);
                    this.isPlaceOrderActionAllowed(true);
                }.bind(this));
            },

            /**
             * called from html
             */
            payOrder: function () {
                this.isPlaceOrderActionAllowed(false);
                this.adapter().pay(function (response) {
                    this.sessionId = response.session.id;
                    this.isPlaceOrderActionAllowed(true);
                    this.placeOrder();
                }.bind(this), function (response) {
                    // Error
                    this.isPlaceOrderActionAllowed(true);
                }.bind(this));
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'session_id': this.sessionId
                    }
                };
            },
       });
    }
);
