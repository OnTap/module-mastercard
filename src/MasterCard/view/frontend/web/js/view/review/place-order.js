define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data'
], function ($, alert, $t, placeOrderAction, redirectOnSuccessAction, quote, checkoutData) {
    'use strict';
    return function (config, element) {
        quote.guestEmail = config.email;
        checkoutData.setValidatedEmailValue(config.email);

        $(element).click(function (event) {
            $('body').trigger('processStart');

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
        });
    };
});
