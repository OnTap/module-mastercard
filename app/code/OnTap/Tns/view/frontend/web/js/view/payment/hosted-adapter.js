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
        amount: null,
        currency: null,
        title: null,
        config: null,
        onLoaded: null,
        errorCallback: null,
        cancelCallback: null,

        configureApi: function (onLoadedCallback) {
            this.onLoaded = onLoadedCallback;
            window.errorCallback = $.proxy(this.errorCallback, this);
            window.cancelCallback = $.proxy(this.cancelCallback, this);

            var node = requirejs.load({
                contextName: '_',
                onScriptLoad: $.proxy(this.onLoad, this)
            }, 'tnshosted', 'https://test-gateway.mastercard.com/checkout/version/32/checkout.js');

            node.setAttribute('data-error', 'window.errorCallback');
            node.setAttribute('data-cancel', 'window.cancelCallback');
        },

        onLoad: function () {
            Checkout.configure({
                merchant: this.config.merchant_username,
                order: {
                    amount: this.amount,
                    currency: this.currency,
                    description: 'Ordered items'
                },
                interaction: {
                    //cancelUrl: 'https://www.local/xxx',
                    merchant: {
                        name: this.title
                    }
                }
            });
            this.onLoaded(this);
        },

        showPayment: function () {
            Checkout.showLightbox();
        }
    };
});
