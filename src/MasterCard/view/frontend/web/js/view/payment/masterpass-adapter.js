/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
/*browser:true*/
/*global define*/
define([
    'jquery'
], function ($) {
    'use strict';
    return {
        loadAdapter: function (componentUrl, onLoadedCallback) {
            var node = requirejs.load({
                contextName: '_',
                onScriptLoad: $.proxy(onLoadedCallback, this)
            }, 'masterpass_wallet', componentUrl);
        },

        checkout: function (data, onSuccess, onCancel, onFailure) {
            MasterPass.client.checkout($.extend({
                'failureCallback': $.proxy(onSuccess, this),
                'cancelCallback': $.proxy(onCancel, this),
                'successCallback': $.proxy(onFailure, this)
            }, data));
        }
    }
});
