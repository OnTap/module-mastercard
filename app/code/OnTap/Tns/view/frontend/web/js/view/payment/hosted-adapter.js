/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
            }, 'tnshosted', componentUrl);

            node.setAttribute('data-error', 'window.tnsErrorCallback');
            node.setAttribute('data-cancel', 'window.tnsCancelCallback');
            node.setAttribute('data-complete', 'window.tnsCompletedCallback');
        },
        configureApi: function (merchant, amount, currency, sessionId, sessionVersion) {
            Checkout.configure({
                merchant: merchant,
                order: {
                    amount: amount,
                    currency: currency,
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
