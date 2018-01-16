/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/model/quote',
        'OnTap_MasterCard/js/view/payment/method-renderer/base-adapter',
        'OnTap_MasterCard/js/action/create-session',
        'OnTap_MasterCard/js/action/open-wallet',
        'Magento_Checkout/js/action/set-payment-information',
        'OnTap_MasterCard/js/action/update-session-from-wallet',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/full-screen-loader',
        'jquery',
        'uiLayout'
    ],
    function (
        quote,
        Component,
        createSessionAction,
        openWalletAction,
        setPaymentInformationAction,
        updateSessionAction,
        globalMessageList,
        loader,
        $,
        layout
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/amex_wallet'
            },
            additionalData: {},
            params: {},

            initChildren: function () {
                this._super();
                var config = this.getConfig();

                var threeDSecureComponent = {
                    parent: this.name,
                    name: this.name + '.threedsecure',
                    displayArea: 'threedsecure',
                    component: 'OnTap_MasterCard/js/view/payment/threedsecure',
                    config: {
                        id: this.item.method,
                        messages: this.messageContainer,
                        checkUrl: config.check_url,
                        onComplete: $.proxy(this.redirectPlaceOrder, this),
                        onError: $.proxy(this.threeDSecureCheckFailed, this),
                        onCancel: $.proxy(this.threeDSecureCancelled, this)
                    }
                };
                layout([threeDSecureComponent]);

                return this;
            },

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
                var amexAuthCode = response.auth_code,
                    transactionId = response.transaction_id,
                    walletId = response.wallet_id,
                    cardType = response.card_type;

                this.params = {
                    authCode: amexAuthCode,
                    transId: transactionId,
                    walletId: walletId,
                    selCardType: cardType
                };

                loader.startLoader();

                var xhr = setPaymentInformationAction(this.messageContainer, this.getData());

                $.when(xhr).done($.proxy(function () {
                    this.placeOrder();
                }, this)).fail(
                    $.proxy(this.paymentFailed, this)
                );
            },

            paymentFailed: function () {
                loader.stopLoader();
            },

            loadAdapter: function () {
                window.aecCallbackHandler = $.proxy(this.aecCallbackHandler, this);
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

            placeOrder: function () {
                var action = updateSessionAction(
                    'mpgs',
                    this.params,
                    this.messageContainer
                );

                $.when(action).done($.proxy(function () {
                    loader.stopLoader();
                    if (this.is3DsEnabled()) {
                        this.delegate('threeDSecureOpen', this);
                    } else {
                        this.redirectPlaceOrder();
                    }
                }, this)).fail($.proxy(this.paymentFailed, this));
            },

            getConfig: function () {
                return window.checkoutConfig.wallets[this.getCode()];
            },

            is3DsEnabled: function () {
                return this.getConfig().three_d_secure;
            },

            threeDSecureCancelled: function () {
                this.isPlaceOrderActionAllowed(true);
            },

            redirectPlaceOrder: function () {
                this.adapterLoaded(false);
                window.location.href = this.getConfig().callback_url + '?' + $.param({
                    guestEmail: quote.guestEmail,
                    quoteId: quote.getQuoteId()
                });
            },

            threeDSecureCheckFailed: function () {
                loader.stopLoader();
            }
        });
    }
);
