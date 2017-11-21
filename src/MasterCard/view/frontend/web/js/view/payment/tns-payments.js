/*
 * Copyright (c) 2016. On Tap Networks Limited.
 */
/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        _,
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'tns_direct',
                component: 'OnTap_MasterCard/js/view/payment/method-renderer/tns-direct'
            },
            {
                type: 'tns_hosted',
                component: 'OnTap_MasterCard/js/view/payment/method-renderer/tns-hosted'
            },
            {
                type: 'tns_hpf',
                component: 'OnTap_MasterCard/js/view/payment/method-renderer/tns-hpf'
            }
        );

        _.each(window.checkoutConfig.payment.amexWallet, function (config, index) {
            rendererList.push(
                {
                    type: index,
                    config: config.config,
                    component: config.component,

                    /**
                     * Custom payment method types comparator
                     * @param {String} typeA
                     * @param {String} typeB
                     * @return {Boolean}
                     */
                    typeComparatorCallback: function (typeA, typeB) {
                        return typeA.substring(0, typeA.lastIndexOf('_')) === typeB;
                    }
                }
            );
        });

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
