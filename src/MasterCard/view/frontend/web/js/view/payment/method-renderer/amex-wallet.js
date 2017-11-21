/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data'
    ],
    function (Component, selectPaymentMethod, checkoutData) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/amex-wallet'
            },

            /**
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                this._super()
                    .observe([]);

                return this;
            },

            /**
             * @returns {String}
             */
            getId: function () {
                return this.index;
            },

            /**
             * @returns
             */
            selectPaymentMethod: function () {
                selectPaymentMethod(
                    {
                        method: this.getId()
                    }
                );
                checkoutData.setSelectedPaymentMethod(this.getId());

                return true;
            }
        });
    }
);
