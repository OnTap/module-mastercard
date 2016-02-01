/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (quote, urlBuilder, storage, url, errorProcessor) {
        'use strict';

        return function (paymentData, messageContainer) {
            var serviceUrl,
                payload;

            serviceUrl = urlBuilder.createUrl('/tns/hc/session/create', {
                quoteId: quote.getQuoteId()
            });
            payload = {
                cartId: quote.getQuoteId(),
                email: quote.guestEmail,
                paymentMethod: paymentData,
                billingAddress: quote.billingAddress()
            };

            return storage.post(
                serviceUrl, JSON.stringify(payload)
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                }
            );
        };
    }
);