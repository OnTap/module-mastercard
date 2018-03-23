define([
    'uiComponent',
    'jquery',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/checkout-data-resolver'
], function (Component, $, registry, quote, checkoutData, createShippingAddress, selectShippingAddress, checkoutDataResolver) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();

            // xxx: data consistency hack
            if (typeof this.shippingFromWallet.region === 'object') {
                this.shippingFromWallet.region = this.shippingFromWallet.region.region;
            }

            registry.async('checkoutProvider')($.proxy(function (checkoutProvider) {
                var shippingAddressData = createShippingAddress(this.shippingFromWallet);
                // var shippingAddressData = checkoutData.getShippingAddressFromData();

                if (shippingAddressData) {
                    checkoutProvider.set(
                        'shippingAddress',
                        $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                    );
                }
                checkoutProvider.on('shippingAddress', function (shippingAddrsData) {
                    checkoutData.setShippingAddressFromData(shippingAddrsData);
                });

                checkoutData.setSelectedShippingAddress(shippingAddressData.getKey());

                checkoutDataResolver.resolveShippingAddress();
            }, this));

            return this;
        }
    });
});
