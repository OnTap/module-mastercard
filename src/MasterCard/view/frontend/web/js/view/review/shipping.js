define([
    'uiComponent',
    'jquery',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address'
], function (Component, $, registry, quote, checkoutData, createShippingAddress, selectShippingAddress) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();

            // xxx: data consistency hack
            if (typeof this.shippingFromWallet.region === 'object') {
                this.shippingFromWallet.region = this.shippingFromWallet.region.region;
            }

            var newShippingAddress = createShippingAddress(this.shippingFromWallet);
            selectShippingAddress(newShippingAddress);
            checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
            checkoutData.setNewCustomerShippingAddress($.extend(true, {}, this.shippingFromWallet));

            return this;
        }
    });
});
