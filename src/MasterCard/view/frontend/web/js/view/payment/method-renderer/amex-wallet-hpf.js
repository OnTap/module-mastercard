/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
/*global define*/
define(
    [
        'OnTap_MasterCard/js/view/payment/method-renderer/base-adapter',
        'OnTap_MasterCard/js/view/payment/hpf-adapter',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'OnTap_MasterCard/js/action/set-billing-address',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (Component, paymentAdapter, $, quote, setBillingAddressAction, globalMessageList, loader) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/amex_wallet'
            },
            additionalData: {},
            adapter: null,

            loadAdapter: function () {
                var config = this.getConfig();
                var totals = quote.totals();

                var data = {
                    wallets: {
                        amexExpressCheckout: {
                            enabled: true,
                            initTags: {
                                'theme': 'responsive',
                                'env': config.env,
                                'disable_btn': 'false',
                                'client_id': config.client_id
                            }
                        }
                    },
                    order: {
                        amount: this.safeNumber(totals.base_grand_total),
                        currency: totals.quote_currency_code
                    },
                    callbacks: {
                        amexExpressCheckout: $.proxy(this.aecInteractionFinished, this)
                    }
                };

                new MutationObserver($.proxy(this.adapterLoaded, this, true))
                    .observe($('#amex-express-checkout').get(0), { childList: true });

                paymentAdapter.init(
                    this.getCode(),
                    config.component_url,
                    data,
                    $.proxy(this.paymentAdapterLoaded, this),
                    config.debug
                );
            },

            paymentAdapterLoaded: function (adapter, response) {
                this.adapter = adapter;
                if (response.status === 'system_error') {
                    this.messageContainer.addErrorMessage({message: response.message});
                }
            },

            getConfig: function () {
                return window.checkoutConfig.wallets[this.getCode()];
            },

            aecInteractionFinished: function (response) {
                if (response.status !== 'ok' || !response.session) {
                    this.messageContainer.addErrorMessage({message: 'Unexpected AEC status, please try again later.'});
                } else {
                    var xhr = setBillingAddressAction(globalMessageList);
                    var params = $.extend(response.session, {
                        guestEmail: quote.guestEmail,
                        quoteId: quote.getQuoteId()
                    });
                    $.when(xhr).done($.proxy(function () {
                        loader.startLoader();
                        window.location.href = this.getConfig().callback_url + '?' + $.param(params);
                    }, this));
                }
            }
        });
    }
);
