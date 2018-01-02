/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
/*global define*/
define(
    [
        'OnTap_MasterCard/js/view/payment/method-renderer/base-adapter',
        'OnTap_MasterCard/js/view/payment/amex-adapter',
        'OnTap_MasterCard/js/action/create-session',
        'OnTap_MasterCard/js/action/open-wallet',
        'jquery'
    ],
    function (Component, adapter, createSessionAction, openWalletAction, $) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/amex_wallet'
            },
            additionalData: {},

            openWallet: function (session) {
                new MutationObserver($.proxy(this.adapterLoaded, this, true))
                    .observe($('#amex-express-checkout').get(0), { childList: true });

                var config = this.getConfig();
                var action = openWalletAction(
                    'mpgs',
                    {
                        'sessionId': session[0],
                        'type': 'AMEX_EXPRESS_CHECKOUT'
                    },
                    this.messageContainer
                );

                $.when(action).fail($.proxy(function () {
                    this.isPlaceOrderActionAllowed(true);
                    this.adapterLoaded(true);
                    this.messageContainer.addErrorMessage({message: "Payment Adapter failed to load"});

                }, this)).done($.proxy(function (response) {
                    var amexInit = $('<amex:init />')
                        .attr('client_id', config.client_id)
                        .attr('theme', 'responsive')
                        .attr('disable_btn', 'false')
                        .attr('env', config.env)
                        .attr('callback', 'aecCallbackHandler');

                    var amexBuy = $('<amex:buy />')
                        .attr('encrypted_data', response.encrypted_data);

                    amexInit.append(amexBuy);
                    $('body').append(amexInit);

                    requirejs.load({
                        contextName: '_'
                    }, 'amex_wallet', this.getConfig().adapter_component);

                    this.isPlaceOrderActionAllowed(true);
                }, this));
            },

            aecCallbackHandler: function (response) {
                var amexAuthCode = response.auth_code;
                var transactionId = response.transaction_id;
                var walletId = response.wallet_id;
                var cardType = response.card_type;

                var params = {
                    'authCode': amexAuthCode,
                    'transactionId': transactionId,
                    'walletId': walletId,
                    'selectedCardType': cardType
                };

                window.location.href = this.getConfig().callback_url + '?' + $.param(params);
            },

            loadAdapter: function () {
                window.aecCallbackHandler = $.proxy(this.aecCallbackHandler, this);

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
                    this.openWallet(session);
                }, this));
            },

            getConfig: function () {
                return window.checkoutConfig.wallets[this.getCode()];
            }
        });
    }
);
