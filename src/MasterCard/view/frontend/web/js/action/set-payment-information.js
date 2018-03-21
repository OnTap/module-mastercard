/*
 * Copyright (c) On Tap Networks Limited.
 */
define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer'
], function (quote, urlBuilder, storage, errorProcessor, customer) {
    'use strict';

    return function (messageContainer, paymentData) {
        var serviceUrl,
            payload;

        /**
         * Checkout for guest and registered customer.
         */
        if (!customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/mpgs/wallet/:cartId/set-payment-information', {
                cartId: quote.getQuoteId()
            });
            payload = {
                cartId: quote.getQuoteId(),
                email: quote.guestEmail,
                paymentMethod: paymentData,
                billingAddress: quote.billingAddress()
            };
        } else {
            serviceUrl = urlBuilder.createUrl('/mpgs/wallet/set-payment-information', {});
            payload = {
                cartId: quote.getQuoteId(),
                paymentMethod: paymentData,
                billingAddress: quote.billingAddress()
            };
        }

        return storage.post(
            serviceUrl, JSON.stringify(payload)
        ).fail(
            function (response) {
                errorProcessor.process(response, messageContainer);
            }
        );
    };
});
