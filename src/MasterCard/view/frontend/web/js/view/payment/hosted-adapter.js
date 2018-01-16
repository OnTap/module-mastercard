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

            var loader = document.createElement('script');
            loader.type = 'text/javascript';
            loader.async = true;
            loader.src = componentUrl;
            loader['data-error'] = 'tnsErrorCallback';
            loader['data-cancel'] = 'tnsCancelCallback';
            loader['data-complete'] = 'tnsCompletedCallback';
            document.body.append(loader);

            this.waitUntilReady($.proxy(onLoadedCallback, this));
        },
        waitUntilReady: function (callback) {
            setTimeout(function() {
                if (typeof window.Checkout !== 'undefined') {
                    callback();
                } else {
                    this.waitUntilReady(callback);
                }
            }.bind(this), 200);
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
