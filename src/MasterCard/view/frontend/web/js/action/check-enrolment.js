/*
 * Copyright (c) 2016. On Tap Networks Limited.
 */
define(
    [
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (quote, storage, errorProcessor) {
        'use strict';
        return function (messageContainer, checkUrl) {
            return storage.post(checkUrl, []).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                }
            );
        };
    }
);
