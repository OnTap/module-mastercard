/*
 * Copyright (c) 2016. On Tap Networks Limited.
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
        'OnTap_MasterCard/js/action/create-session',
        'mage/translate'
    ],
    function (Component, $, ko, quote, fullScreenLoader, alert, paymentAdapter, createSessionAction, $t) {
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

            createPaymentSession: function () {
                this.isPlaceOrderActionAllowed(false);
                this.buttonTitle(this.buttonTitleDisabled);

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
                            quote,
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
                this.placeOrder();
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
