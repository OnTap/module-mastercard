/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'tnshosted'
], function ($) {
    'use strict';

    return {
        configureApi: function () {
            Checkout.configure({
                merchant: 'xxx',
                order: {
                    amount: 100.00,
                    currency: 'GBP',
                    description: 'Ordered goods'
                },
                interaction: {
                    //cancelUrl: 'http://xxx.local/xxx',
                    merchant: {
                        name: 'Merchant X'
                    }
                }
            });
        },
        showLightbox: function () {
            Checkout.showLightbox();
        }
    };
});
