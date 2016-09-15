/*
 * Copyright (c) 2016. On Tap Networks Limited.
 */
/*browser:true*/
/*global define*/
define([
    'jquery'
], function ($) {
    'use strict';

    return {
        onLoadedCallback: null,
        sessionUpdatedCallback: null,
        debug: false,
        fields: null,

        logDebug: function (s) {
            if (this.debug) {
                console.info(s);
            }
        },
        loadApi: function (fields, componentUrl, merchantId, onLoadedCallback, debugMode) {
            this.onLoadedCallback = onLoadedCallback;
            this.debug = debugMode;
            this.fields = fields;

            this.logDebug("Loading HPF Api...");

            var url = componentUrl,
                cacheBust = new Date().getTime();

            if (this.debug) {
                url += '?debug=1&_=' + cacheBust;
            } else {
                url += '?_=' + cacheBust;
            }
            requirejs.load({
                contextName: '_',
                onScriptLoad: $.proxy(this.scriptLoadedCallback, this)
            }, 'tns_hpf', url);

        },
        scriptLoadedCallback: function () {
            this.logDebug("Script loaded, configuring session...");
            PaymentSession.configure({
                fields: this.fields,
                frameEmbeddingMitigation: ["x-frame-options"],
                callbacks: {
                    initialized: $.proxy(this.onLoadedCallback, this),
                    formSessionUpdate: $.proxy(this.sessionUpdated, this)
                }
            });
        },
        startSession: function (callback) {
            this.logDebug("Starting payment session...");
            this.sessionUpdatedCallback = callback;
            PaymentSession.updateSessionFromForm();
        },
        sessionUpdated: function (response) {
            this.logDebug("Session response received");
            this.sessionUpdatedCallback(response);
        }
    };
});
