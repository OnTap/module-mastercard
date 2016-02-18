/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, VaultComponent, globalMessageList, fullScreenLoader) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'OnTap_Tns/payment/vault'
        },

        /**
         * Get current Braintree vault id
         * @returns {String}
         */
        getId: function () {
            return 'tns_direct_' + this.index;
        },

        /**
         * Get last 4 digits of card
         * @returns {String}
         */
        getMaskedCard: function () {
            return this.details.cc_number;
        },

        /**
         * Get expiration date
         * @returns {String}
         */
        getExpirationDate: function () {
            return this.details.cc_expr_month + '/' + this.details.cc_expr_year;
        },

        /**
         * Get card type
         * @returns {String}
         */
        getCardType: function () {
            return this.details.cc_type;
        },

        /**
         * Place order
         */
        placeOrder: function () {
            //this.getPaymentMethodNonce();
        },

        getIcons: function (icon) {
            var map = {
                MASTERCARD: 'MC',
                VISA: 'VI'
                // @todo: add more icons
            };
            return this._super(map[icon] ? map[icon] : icon);
        }

        /**
         * Send request to get payment method nonce
         */
        //getPaymentMethodNonce: function () {
        //    var self = this;
        //
        //    fullScreenLoader.startLoader();
        //    $.get(self.nonceUrl, {
        //            'public_hash': self.publicHash
        //        })
        //        .done(function (response) {
        //            fullScreenLoader.stopLoader();
        //            self.hostedFields(function (formComponent) {
        //                formComponent.setPaymentMethodNonce(response.paymentMethodNonce);
        //                formComponent.additionalData['public_hash'] = self.publicHash;
        //                formComponent.code = 'vault';
        //                formComponent.placeOrder();
        //            });
        //        })
        //        .fail(function (response) {
        //            var error = JSON.parse(response.responseText);
        //
        //            fullScreenLoader.stopLoader();
        //            globalMessageList.addErrorMessage({
        //                message: error.message
        //            });
        //        });
        //}
    });
});
