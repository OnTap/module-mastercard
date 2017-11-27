/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
/*global define*/
define(
    [
        'OnTap_MasterCard/js/view/payment/method-renderer/base-adapter',
        'OnTap_MasterCard/js/action/create-session',
        'jquery'
    ],
    function (Component, createSessionAction, $) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/wallet'
            },
            additionalData: {},

            createPaymentSession: function () {
                this.isPlaceOrderActionAllowed(false);
                this.buttonTitle(this.buttonTitleDisabled);

                var action = createSessionAction(
                    'mpgs',
                    this.getData(),
                    this.messageContainer
                );

                $.when(action).fail($.proxy(function () {
                    this.isPlaceOrderActionAllowed(true);
                }, this)).done($.proxy(function (session) {
                    // Session creation succeeded
                    if (this.active() && this.adapterLoaded()) {

                        console.log('Session created', session, this);

                    } else {
                        this.isPlaceOrderActionAllowed(true);
                        this.messageContainer.addErrorMessage({message: "Payment Adapter failed to load"});
                    }
                }, this));
            },

            loadAdapter: function () {
                this.adapterLoaded(true);
            },

            getConfig: function () {
                return window.checkoutConfig.wallets[this.getCode()];
            }
        });
    }
);
