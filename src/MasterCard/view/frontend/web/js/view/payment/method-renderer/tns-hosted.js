/*
 * Copyright (c) 2016. On Tap Networks Limited.
 */
/*global define*/
define(
    [
        'OnTap_MasterCard/js/view/payment/method-renderer/base-adapter',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/modal/alert',
        'OnTap_MasterCard/js/view/payment/hosted-adapter',
        'OnTap_MasterCard/js/action/create-session',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function (
        Component,
        $,
        quote,
        fullScreenLoader,
        alert,
        paymentAdapter,
        createSessionAction,
        additionalValidators
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/tns-hosted'
            },
            resultIndicator: null,
            sessionVersion: null,

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
                if (!additionalValidators.validate()) {
                    return;
                }

                this.isPlaceOrderActionAllowed(false);
                this.buttonTitle(this.buttonTitleDisabled);

                var action = createSessionAction(
                    'mpgs/hosted',
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
