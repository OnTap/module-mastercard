/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
/*global define*/
/*global V*/
define(
    [
        'jquery',
        'OnTap_MasterCard/js/view/payment/method-renderer/base-adapter',
        'OnTap_MasterCard/js/action/create-session',
        'OnTap_MasterCard/js/action/update-session-from-wallet',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, Component, createSessionAction, updateSessionAction, quote) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/visa_wallet'
            },

            loadAdapter: function () {
                this.isPlaceOrderActionAllowed(false);
                window.onVisaCheckoutReady = $.proxy(this.onVisaCheckoutReady, this);

                requirejs.load({
                    config: {},
                    contextName: '_'
                }, 'visa_checkout', this.getConfig().sdk_component);

                // Background operation, user interaction is allowed
                createSessionAction(
                    'mpgs',
                    this.getData(),
                    this.messageContainer
                );
            },

            onVisaCheckoutReady: function () {
                V.init({
                    apikey: this.getConfig().api_key,
                    paymentRequest: {
                        subtotal: quote.totals().grand_total,
                        currencyCode: quote.totals().quote_currency_code
                    }
                });

                V.on("payment.success", $.proxy(this.onPaymentSuccess, this));
                V.on("payment.cancel", $.proxy(this.onPaymentCancel, this));
                V.on("payment.error", $.proxy(this.onPaymentError, this));

                this.isPlaceOrderActionAllowed(true);
                this.adapterLoaded(true);
            },

            onPaymentSuccess: function (payment) {
                var xhr = updateSessionAction(
                    'mpgs',
                    'visa',
                    {
                        callId: payment.callid
                    },
                    this.messageContainer
                );
                console.log('success', payment);
            },

            onPaymentCancel: function (payment) {
                // @todo: implement
                console.log('cancelled', payment);
            },

            onPaymentError: function (error) {
                // @todo: implement
                console.log('error', error);
            },

            getConfig: function () {
                return window.checkoutConfig.wallets[this.getCode()];
            }
        });
    }
);
