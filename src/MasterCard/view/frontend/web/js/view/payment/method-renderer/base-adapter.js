/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'mage/translate',
        'jquery'
    ],
    function (Component, selectPaymentMethod, checkoutData, $t, $) {
        'use strict';
        return Component.extend({
            defaults: {
                template: null,
                adapterLoaded: false,
                active: false,
                buttonTitle: null,
                buttonTitleEnabled: $t('Pay'),
                buttonTitleDisabled: $t('Please wait...'),
                imports: {
                    onActiveChange: 'active'
                }
            },

            /**
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                this._super()
                    .observe('active adapterLoaded buttonTitle');

                this.buttonTitle(this.buttonTitleDisabled);
                this.isPlaceOrderActionAllowed.subscribe($.proxy(this.buttonTitleHandler, this));
                this.adapterLoaded.subscribe($.proxy(this.buttonTitleHandler, this));
                return this;
            },

            buttonTitleHandler: function (isButtonEnabled) {
                if (isButtonEnabled && this.isActive()) {
                    this.buttonTitle(this.buttonTitleEnabled);
                }
            },

            onActiveChange: function (isActive) {
                if (isActive && !this.adapterLoaded()) {
                    this.loadAdapter();
                }
            },

            isCheckoutDisabled: function () {
                return !this.adapterLoaded() || !this.isPlaceOrderActionAllowed();
            },

            getConfig: function () {
                return window.checkoutConfig.payment[this.getCode()];
            },

            isActive: function () {
                var active = this.getCode() === this.isChecked();
                this.active(active);
                return active;
            },

            createPaymentSession: function () {
                console.warn('%s createPaymentSession() not implemented', this.getCode());
            },

            loadAdapter: function () {
                console.warn('%s loadAdapter() not implemented', this.getCode());
            }
        });
    }
);
