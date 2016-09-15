/*
 * Copyright (c) 2016. On Tap Networks Limited.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert'
], function ($, Class, alert) {
    'use strict';

    return Class.extend({
        defaults: {
            $selector: null,
            selector: 'edit_form'
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;

            self.$selector = $('#' + self.selector);
            self.$selector.on(
                'setVaultNotActive',
                function () {
                    self.$selector.off('submitOrder.tns_direct_vault');
                }
            );
            this._super();

            this.initEventHandlers();

            return this;
        },

        /**
         * Get payment code
         * @returns {String}
         */
        getCode: function () {
            return 'tns_direct';
        },

        /**
         * Init event handlers
         */
        initEventHandlers: function () {
            $('#' + this.container).find('[name="payment[token_switcher]"]')
                .on('click', this.selectPaymentMethod.bind(this));
        },

        /**
         * Select current payment token
         */
        selectPaymentMethod: function () {
            this.disableEventListeners();
            this.enableEventListeners();
        },

        /**
         * Enable form event listeners
         */
        enableEventListeners: function () {
            this.$selector.on('submitOrder.tns_direct_vault', this.submitOrder.bind(this));
        },

        /**
         * Disable form event listeners
         */
        disableEventListeners: function () {
            this.$selector.off('submitOrder');
        },

        /**
         * Pre submit for order
         * @returns {Boolean}
         */
        submitOrder: function () {
            this.$selector.validate().form();
            this.$selector.trigger('afterValidate.beforeSubmit');
            $('body').trigger('processStop');

            // validate parent form
            if (this.$selector.validate().errorList.length) {
                return false;
            }

            this.$selector.find('[name="payment[public_hash]"]').val(this.publicHash);
            this.$selector.trigger('realOrder');

        },

        /**
         * Show alert message
         * @param {String} message
         */
        error: function (message) {
            alert({
                content: message
            });
        }
    });
});
