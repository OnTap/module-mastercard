/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'tnshpf'
], function ($) {
    'use strict';

    return {
        configureApi: function () {
            HostedForm.setMerchant('xxx');
        },

        startSession: function (sessionDetails, callback) {
            HostedForm.createSession(sessionDetails, callback);
        }
    };
});
