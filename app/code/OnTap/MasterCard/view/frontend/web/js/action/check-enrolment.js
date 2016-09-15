/*
 * Copyright (c) 2016. On Tap Networks Limited.
 */
define(
    [
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (quote, storage, url, errorProcessor) {
        'use strict';
        return function (messageContainer) {
            return storage.post(url.build('tns/threedsecure/check/'), []).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                }
            );
        };
    }
);
