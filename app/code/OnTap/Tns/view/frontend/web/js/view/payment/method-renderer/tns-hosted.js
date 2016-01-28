/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/modal/alert',
        'OnTap_Tns/js/view/payment/hosted-adapter'
    ],
    function (Component, $, ko, quote, fullScreenLoader, alert, paymentAdapter) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'OnTap_Tns/payment/tns-hosted',
                adapterLoaded: false,
                active: false,
                imports: {
                    onActiveChange: 'active'
                }
            },

            initObservable: function () {
                this._super()
                    .observe('active adapterLoaded');

                return this;
            },

            onActiveChange: function (isActive) {
                if (isActive && !this.adapterLoaded()) {
                    this.loadAdapter();
                }
            },

            isActive: function () {
                var active = this.getCode() === this.isChecked();
                this.active(active);
                return active;
            },

            loadAdapter: function () {
                paymentAdapter.errorCallback = $.proxy(this.errorCallback, this);
                paymentAdapter.cancelCallback = $.proxy(this.cancelCallback, this);
                paymentAdapter.config = this.getConfig();
                paymentAdapter.currency = quote.totals().quote_currency_code;
                paymentAdapter.amount = quote.totals().grand_total.toFixed(2);
                paymentAdapter.title = this.getTitle();

                paymentAdapter.configureApi($.proxy(this.paymentAdapterLoaded, this));
            },

            paymentAdapterLoaded: function (adapter) {
                this.adapterLoaded(true);
            },

            isCheckoutDisabled: function () {
                return !this.adapterLoaded() || !this.isPlaceOrderActionAllowed();
            },

            getConfig: function () {
                return window.checkoutConfig.payment[this.getCode()];
            },

            showPayment: function () {
                fullScreenLoader.startLoader();
                paymentAdapter.showPayment();
            },

            errorCallback: function (error) {
                fullScreenLoader.stopLoader();
                alert({
                    content: error.cause + ': ' + error.explanation
                });
            },

            cancelCallback: function () {
                fullScreenLoader.stopLoader();
                alert({
                    content: 'Payment cancelled.'
                });
            }
        });
    }
);
