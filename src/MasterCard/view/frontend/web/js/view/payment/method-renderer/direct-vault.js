/*
 * Copyright (c) 2016-2019 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
                    'csc',
                    'useCcv'
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
