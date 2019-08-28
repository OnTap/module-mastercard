/*
 * Copyright (c) On Tap Networks Limited.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'mage/translate'
], function ($, VaultComponent, $t) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'OnTap_MasterCard/payment/direct-vault',
            active: false,
            csc: null
        },

        initObservable: function () {
            this._super()
                .observe([
                    'active',
                    'csc'
                ]);
            return this;
        },

        /**
         * @returns {String}
         */
        getId: function () {
            return 'tns_direct_vault_' + this.index;
        },

        /**
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
            return this.details.type;
        },

        /**
         * @returns {String}
         */
        getToken: function () {
            return this.publicHash;
        },

        getCvvImageHtml: function() {
            return '<img src="' + this.getCvvImageUrl()
                + '" alt="' + $t('Card Verification Number Visual Reference')
                + '" title="' + $t('Card Verification Number Visual Reference')
                + '" />';
        },

        getCvvImageUrl: function() {
            return window.checkoutConfig.payment.ccform.cvvImageUrl[this.getCode()];
        },

        getData: function () {
            var data = this._super();
            data['additional_data']['cc_cid'] = this.csc();

            return data;
        },

        isActive: function () {
            var active = this.getId() === this.isChecked();
            this.active(active);
            return active;
        },

    });
});
