define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data'
], function ($, alert, $t, setShippingInformationAction, placeOrderAction, redirectOnSuccessAction, quote, checkoutData) {
    'use strict';
    return function (config, element) {
        if (!window.isCustomerLoggedIn) {
            quote.guestEmail = config.email;
            checkoutData.setValidatedEmailValue(config.email);
        }

        $(element).click(function (event) {
            if (!quote.shippingMethod()) {
                alert({
                    content: $t('Please select a shipping method.')
                });
                return;
            }

            var method = quote.shippingMethod()['method_code'],
                carrier = quote.shippingMethod()['carrier_code'];

            if (!method || !carrier) {
                alert({
                    content: $t('Please select a shipping method.')
                });
                return;
            }

            $('body').trigger('processStart');

            var action = setShippingInformationAction();
            $.when(action).done(function () {
                var action = placeOrderAction({
                    method: config.method
                });

                $.when(action).done(function () {
                    redirectOnSuccessAction.execute();
                }).fail(function () {
                    $('body').trigger('processStop');
                    alert({
                        content: $t('Payment could not be completed, please try again later.')
                    });
                });
            }).fail(function () {
                $('body').trigger('processStop');
                alert({
                    content: $t('Failed saving shipping address, please try again later.')
                });
            });
        });
    };
});
