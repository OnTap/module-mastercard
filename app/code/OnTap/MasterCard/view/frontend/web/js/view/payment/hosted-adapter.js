/*
 * Copyright (c) 2016. On Tap Networks Limited.
 */
/*browser:true*/
/*global define*/
define([
    'jquery'
], function ($) {
    'use strict';

    return {
        loadApi: function (componentUrl, onLoadedCallback, onError, onCancel, onComplete) {
            window.tnsErrorCallback = $.proxy(onError, this);
            window.tnsCancelCallback = $.proxy(onCancel, this);
            window.tnsCompletedCallback = $.proxy(onComplete, this);

            var node = requirejs.load({
                contextName: '_',
                onScriptLoad: $.proxy(onLoadedCallback, this)
            }, 'tns_hosted', componentUrl);

            node.setAttribute('data-error', 'window.tnsErrorCallback');
            node.setAttribute('data-cancel', 'window.tnsCancelCallback');
            node.setAttribute('data-complete', 'window.tnsCompletedCallback');
        },
        safeNumber: function (num) {
            return parseFloat(num).toFixed(2);
        },
        configureApi: function (merchant, quote, sessionId, sessionVersion) {
            var totals = quote.totals();
            Checkout.configure({
                merchant: merchant,
                order: {
                    amount: this.safeNumber(totals.base_grand_total),
                    currency: totals.quote_currency_code,
                    description: 'Ordered items'
                },
                interaction: {
                    merchant: {
                        name: 'Magento'
                    },
                    displayControl: {
                        customerEmail: 'HIDE',
                        billingAddress: 'HIDE',
                        orderSummary: 'HIDE',
                        paymentTerms: 'HIDE',
                        shipping: 'HIDE'
                    }
                },
                session: {
                    id: sessionId,
                    version: sessionVersion
                }
            });
        },
        showPayment: function () {
            Checkout.showLightbox();
        }
    };
});
