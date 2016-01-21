/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'Magento_Checkout/js/model/full-screen-loader',
        'OnTap_Tns/js/view/payment/adapter'
    ],
    function (Component, ko, fullScreenLoader, paymentAdapter) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'OnTap_Tns/payment/tns-hosted',
                paymentReady: false
            },
            redirectAfterPlaceOrder: false,
            isInAction: true,

            /**
             * @return {exports}
             */
            initObservable: function () {
                this._super()
                    .observe('paymentReady');

                paymentAdapter.configureApi();

                return this;
            },

            overlayEmerge: function () {
                paymentAdapter.showLightbox();
            },

            /**
             * @return {*}
             */
            isPaymentReady: function () {
                return this.paymentReady();
            },

            /**
             * Get action url for payment method iframe.
             * @returns {String}
             */
            getActionUrl: function () {
                return this.isInAction() ? 'http://www.ee' : '';
            },

            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                this.paymentReady(true);
            },

            /**
             * Hide loader when iframe is fully loaded.
             */
            iframeLoaded: function () {
                fullScreenLoader.stopLoader();
            }
        });
    }
);
