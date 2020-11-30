/*
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
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/modal/alert',
        'OnTap_MasterCard/js/view/payment/hosted-adapter',
        'Magento_Checkout/js/action/set-payment-information',
        'OnTap_MasterCard/js/action/create-session',
        'mage/translate'
    ],
    function (Component, $, ko, quote, fullScreenLoader, alert, paymentAdapter, setPaymentInformationAction, createSessionAction, $t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/tns-hosted',
                adapterLoaded: false,
                active: false,
                buttonTitle: null,
                buttonTitleEnabled: $t('Pay'),
                buttonTitleDisabled: $t('Please wait...'),
                imports: {
                    onActiveChange: 'active'
                }
            },
            resultIndicator: null,
            sessionVersion: null,

            initObservable: function () {
                this._super()
                    .observe('active adapterLoaded buttonTitle');

                this.buttonTitle(this.buttonTitleDisabled);
                this.isPlaceOrderActionAllowed.subscribe($.proxy(this.buttonTitleHandler, this));
                this.adapterLoaded.subscribe($.proxy(this.buttonTitleHandler, this));

                return this;
            },

            buttonTitleHandler: function (isButtonEnabled) {
                if (isButtonEnabled && this.isActive()) {
                    this.buttonTitle(this.buttonTitleEnabled);
                }
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

            loadAdapter: function (sessionId) {
                var config = this.getConfig();

                paymentAdapter.loadApi(
                    config.component_url,
                    $.proxy(this.paymentAdapterLoaded, this),
                    $.proxy(this.errorCallback, this),
                    $.proxy(this.cancelCallback, this),
                    $.proxy(this.completedCallback, this)
                );
            },

            paymentAdapterLoaded: function (adapter) {
                this.adapterLoaded(true);
            },

            savePaymentAndCheckout: function () {
                this.isPlaceOrderActionAllowed(false);
                this.buttonTitle(this.buttonTitleDisabled);

                var action = setPaymentInformationAction(this.messageContainer, this.getData());

                $.when(action).fail($.proxy(function () {
                    fullScreenLoader.stopLoader();
                    this.isPlaceOrderActionAllowed(true);
                }, this)).done(
                    this.createPaymentSession.bind(this)
                );
            },

            createPaymentSession: function () {
                var action = createSessionAction(
                    this.getData(),
                    this.messageContainer
                );

                $.when(action).fail($.proxy(function () {
                    // Failed creating session
                    this.isPlaceOrderActionAllowed(true);
                }, this)).done($.proxy(function (session) {
                    // Session creation succeeded
                    if (this.active() && this.adapterLoaded()) {
                        fullScreenLoader.startLoader();

                        var config = this.getConfig();

                        paymentAdapter.configureApi(
                            config.merchant_username,
                            session[0],
                            session[1]
                        );

                        paymentAdapter.showPayment();
                    } else {
                        this.isPlaceOrderActionAllowed(true);
                        this.messageContainer.addErrorMessage({message: "Payment Adapter failed to load"});
                    }
                }, this));
            },

            isCheckoutDisabled: function () {
                return !this.adapterLoaded() || !this.isPlaceOrderActionAllowed();
            },

            getConfig: function () {
                return window.checkoutConfig.payment[this.getCode()];
            },

            errorCallback: function (error) {
                this.isPlaceOrderActionAllowed(true);
                fullScreenLoader.stopLoader();
                alert({
                    content: error.cause + ': ' + error.explanation
                });
            },

            cancelCallback: function () {
                this.isPlaceOrderActionAllowed(true);
                fullScreenLoader.stopLoader();
                alert({
                    content: 'Payment cancelled.'
                });
            },

            completedCallback: function(resultIndicator, sessionVersion) {
                this.resultIndicator = resultIndicator;
                this.sessionVersion = sessionVersion;
                this.isPlaceOrderActionAllowed(true);
                this.placeOrder();
                fullScreenLoader.stopLoader();
            },

            /**
             * Get payment method data
             */
            getData: function() {
                var data = this._super();
                data['additional_data'] = {
                    resultIndicator: this.resultIndicator,
                    sessionVersion: this.sessionVersion
                };
                return data;
            }
        });
    }
);
