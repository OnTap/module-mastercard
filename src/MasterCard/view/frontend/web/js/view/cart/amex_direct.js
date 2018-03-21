/**
 * Amex Direct Shortcut button
 */
define([
    'jquery',
    'OnTap_MasterCard/js/action/create-session',
    'OnTap_MasterCard/js/action/open-wallet',
    'OnTap_MasterCard/js/action/update-session-from-wallet'
], function ($, createSessionAction, openWalletAction, updateSessionFromWalletAction) {
    'use strict';
    $.widget('mpgs.amex_direct', {
        options: {
            config: {},
            method: 'tns_direct_amex',
            walletType: 'AMEX_EXPRESS_CHECKOUT',
            loaderSel: '#amex-direct-loader',
            reviewPageUrl: null
        },

        _create: function () {
            window.aecCallbackHandler = $.proxy(this.aecCallbackHandler, this);

            var action = createSessionAction(
                'mpgs',
                {
                    method: this.options.method
                }
            );
            $.when(action).done($.proxy(this.sessionCreated, this));
        },

        startLoader: function () {
            $('body').trigger('processStart');
        },

        stopLoader: function () {
            $('body').trigger('processStop');
        },

        sessionCreated: function (session) {
            var xhr = openWalletAction(
                'mpgs',
                {
                    sessionId: session[0],
                    type: this.options.walletType
                }
            );
            $.when(xhr).done($.proxy(this.walletCreated, this));
        },

        walletCreated: function (wallet) {
            new MutationObserver($.proxy(this.removeLoader, this))
                .observe($('#amex-express-checkout').get(0), { childList: true });

            var amexInit = $('<amex:init />')
                .attr('client_id', this.options.config.client_id)
                .attr('theme', 'responsive')
                .attr('disable_btn', 'false')
                .attr('env', this.options.config.env)
                .attr('callback', 'aecCallbackHandler');

            var amexBuy = $('<amex:buy />')
                .attr('encrypted_data', wallet.encrypted_data);

            amexInit.append(amexBuy);
            this.element.append(amexInit);

            requirejs.load({
                config: {},
                contextName: '_'
            }, 'amex_wallet', this.options.config.adapter_component);
        },

        removeLoader: function () {
            $(this.options.loaderSel).remove();
        },

        aecCallbackHandler: function (response) {
            this.startLoader();

            var action = updateSessionFromWalletAction('mpgs', {
                authCode: response.auth_code,
                transId: response.transaction_id,
                walletId: response.wallet_id,
                selCardType: response.card_type
            });

            $.when(action)
                .done($.proxy(this.sessionFullyUpdated, this))
                .fail(this.stopLoader);
        },

        sessionFullyUpdated: function (response) {
            var params = $.param({
                session_version: response.session_version
            });
            window.location.href = this.options.reviewPageUrl + '?' + params;
        }
    });
    return $.mpgs.amex_direct;
});
