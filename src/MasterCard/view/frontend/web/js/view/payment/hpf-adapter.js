/*
 * Copyright (c) 2016. On Tap Networks Limited.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
    'use strict';

    return {
        sessionUpdatedCallback: null,
        debug: false,

        logDebug: function (s) {
            if (this.debug) {
                console.info(s);
            }
        },
        getUrl: function (componentUrl) {
            var url = componentUrl,
                cacheBust = new Date().getTime();

            if (this.debug) {
                url += '?debug=1&_=' + cacheBust;
            } else {
                url += '?_=' + cacheBust;
            }

            return url;
        },
        init: function (methodCode, componentUrl, data, onLoadedCallback, debugMode) {
            this.debug = debugMode;

            this.logDebug("Loading HPF...");

            var url = this.getUrl(componentUrl);

            requirejs.load({
                config: {},
                contextName: '_',
                onScriptLoad: $.proxy(function () {
                    $.proxy(this.configure, this)(PaymentSession, data, onLoadedCallback);
                }, this)
            }, methodCode, url);
        },
        configure: function (PaymentSession, data, onLoadedCallback) {
            this.logDebug("Configuring HPF...");

            PaymentSession.configure($.extend({
                callbacks: {
                    initialized: $.proxy(onLoadedCallback, this, this),
                    formSessionUpdate: $.proxy(this.sessionUpdated, this)
                },
                frameEmbeddingMitigation: ["x-frame-options"]
            }, data));
        },
        startSession: function (callback) {
            this.logDebug("Starting payment session...");
            this.sessionUpdatedCallback = callback;
            PaymentSession.updateSessionFromForm('card');
        },
        sessionUpdated: function (response) {
            this.logDebug("Session response received");
            this.sessionUpdatedCallback(response);
        }
    };
});
