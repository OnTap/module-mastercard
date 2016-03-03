/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery'
], function ($) {
    'use strict';

    return {
        onLoadedCallback: null,
        debug: false,

        logDebug: function (s) {
            if (this.debug) {
                console.info(s);
            }
        },
        loadApi: function (componentUrl, merchantId, onLoadedCallback, debugMode) {
            this.onLoadedCallback = onLoadedCallback;
            this.debug = debugMode;

            this.logDebug("Loading HPF Api...");

            var url = componentUrl + merchantId + '/session.js';
            if (this.debug) {
                url += '?debug=1';
            }
            requirejs.load({
                contextName: '_',
                onScriptLoad: $.proxy(this.scriptLoadedCallback, this)
            }, 'tns_hpf', url);

        },
        scriptLoadedCallback: function () {
            this.logDebug("Script loaded, configuring session...");
            PaymentSession.configure({
                fields: {
                    cardNumber: "#tns_hpf_cc_number",
                    securityCode: "#tns_hpf_cc_cid",
                    expiryMonth: "#tns_hpf_expiration",
                    expiryYear: "#tns_hpf_expiration_yr"
                },
                frameEmbeddingMitigation: ["x-frame-options"],
                callbacks: {
                    initialized: $.proxy(this.onLoadedCallback, this),
                    formSessionUpdate: function(response) {
                        console.log('formSessionUpdate %o', response);
                    }
                }
            });
        },
        startSession: function (sessionDetails, callback) {
            this.logDebug("Starting payment session...");
            PaymentSession.updateSessionFromForm();
        }
    };
});
