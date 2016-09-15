/*
 * Copyright (c) 2016. On Tap Networks Limited.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
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
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
