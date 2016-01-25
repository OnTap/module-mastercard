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
                    amount: function() {
                        return 100.00;
                    },
                    currency: 'USD',
                    description: 'Ordered goods'
                },
                interaction: {
                    merchant: {
                        name: 'xxx'
                    }
                }
            });
        },
        showLightbox: function () {
            Checkout.showLightbox();
        }
    };
});
