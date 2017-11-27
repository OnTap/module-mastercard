/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
/*global define*/
define(
    [
        'OnTap_MasterCard/js/view/payment/method-renderer/base-adapter',
        'OnTap_MasterCard/js/view/payment/masterpass-adapter',
        'OnTap_MasterCard/js/action/create-session',
        'OnTap_MasterCard/js/action/open-wallet',
        'jquery'
    ],
    function (Component, adapter, createSessionAction, openWalletAction, $) {
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
                        this.configureWallet(session);

                    } else {
                        this.isPlaceOrderActionAllowed(true);
                        this.messageContainer.addErrorMessage({message: "Payment Adapter failed to load"});
                    }
                }, this));
            },

            configureWallet: function (session) {
                var action = openWalletAction(
                    'mpgs',
                    {
                        'sessionId': session[0],
                        'type': 'MASTERPASS_ONLINE'
                    },
                    this.messageContainer
                );

                $.when(action).fail($.proxy(function () {
                    this.isPlaceOrderActionAllowed(true);
                }, this)).done($.proxy(function (response) {

                    console.log('Open wallet', response);
                    adapter.checkout({
                        'merchantCheckoutId': response.merchant_checkout_id,
                        'allowedCardTypes': [response.allowed_card_types],
                        'requestToken': response.request_token,
                        'failureCallback': response.origin_url,
                        'successCallback': response.origin_url
                    });

                }, this));
            },

            loadAdapter: function () {
                var config = this.getConfig();
                adapter.loadAdapter(config.adapter_component, $.proxy(this.adapterLoaded, this));
            },

            adapterLoaded: function () {
                this.adapterLoaded(true);
            },

            getConfig: function () {
                return window.checkoutConfig.wallets[this.getCode()];
            }
        });
    }
);
