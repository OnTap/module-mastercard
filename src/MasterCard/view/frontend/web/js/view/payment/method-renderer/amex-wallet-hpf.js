/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
/*global define*/
define(
    [
        'OnTap_MasterCard/js/view/payment/method-renderer/base-adapter',
        'OnTap_MasterCard/js/view/payment/hpf-adapter',
        'jquery',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, paymentAdapter, $, quote) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/amex_wallet'
            },
            additionalData: {},
            adapter: null,

            createPaymentSession: function () {
                this.adapter.startSession($.proxy(function(response) {
                    console.log(response);
                }, this));
            },

            loadAdapter: function () {
                var config = this.getConfig();
                var totals = quote.totals();

                var data = {
                    wallets: {
                        amexExpressCheckout: {
                            enabled: true,
                            initTags: {
                                "theme": "responsive",
                                "env": "qa",
                                "disable_btn": "false",
                                "button_color": "light",
                                "client_id": "398f9858-5567-434f-a929-242d6fc7fea8",
                                "display_type":"custom"  // IF USING OWN IMAGE FOR BUTTON
                            }
                        }
                    },
                    order: {
                        amount: this.safeNumber(totals.base_grand_total),
                        currency: totals.quote_currency_code
                    }
                };

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
                this.adapterLoaded(true);
            },

            getConfig: function () {
                return window.checkoutConfig.wallets[this.getCode()];
            }
        });
    }
);
