/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
                component: 'OnTap_Tns/js/view/payment/method-renderer/tns-direct'
            },
            {
                type: 'tns_hosted',
                component: 'OnTap_Tns/js/view/payment/method-renderer/tns-hosted'
            },
            {
                type: 'tns_hpf',
                component: 'OnTap_Tns/js/view/payment/method-renderer/tns-hpf'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
