/*
 * Copyright (c) 2018. On Tap Networks Limited.
 */
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (quote, urlBuilder, storage, errorProcessor) {
        'use strict';

        return function (api, wallet, payload, messageContainer) {
            var serviceUrl = urlBuilder.createUrl('/:api/wallet/:walletType/update-session', {
                api: api,
                walletType: wallet
            });
            return storage.post(serviceUrl, JSON.stringify(payload)).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                }
            );
        };
    }
);
