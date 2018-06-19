/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
/*global define*/
define(
    [
        'jquery',
        'OnTap_MasterCard/js/view/payment/method-renderer/base-adapter',
        'OnTap_MasterCard/js/action/create-session',
        'OnTap_MasterCard/js/action/open-wallet'
    ],
    function ($, Component, createSessionAction, openWalletAction) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/visa_wallet'
            },

            loadAdapter: function () {
                this.isPlaceOrderActionAllowed(false);

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
            
            openWallet: function (session) {
                var action = openWalletAction(
                    'mpgs',
                    {
                        'sessionId': session[0],
                        'type': 'MASTERPASS_ONLINE'
                    },
                    this.messageContainer
                );

                $.when(action).then(
                    $.proxy(this.onOpenWalletData, this),
                    $.proxy(this.onOpenWalletError, this)
                );
            },

            onOpenWalletData: function () {
                console.log('onOpenWalletData %o', arguments);
                this.isPlaceOrderActionAllowed(true);
                this.adapterLoaded(true);
            },

            onOpenWalletError: function () {
                console.log('onOpenWalletError %o', arguments);
                this.isPlaceOrderActionAllowed(true);
                this.adapterLoaded(true);
            }
        });
    }
);
