/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer'
    ],
    function (quote, urlBuilder, storage, url, errorProcessor, customer) {
        'use strict';

        return function (api, sessionData, messageContainer) {
            var serviceUrl,
                payload;

            if (customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/:api/session/wallet', {
                    api: api
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    sessionId: sessionData.sessionId,
                    type: sessionData.type
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/:api/session/:quoteId/wallet', {
                    api: api,
                    quoteId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    email: quote.guestEmail,
                    sessionId: sessionData.sessionId,
                    type: sessionData.type
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
    }
);
