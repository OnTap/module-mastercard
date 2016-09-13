/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
