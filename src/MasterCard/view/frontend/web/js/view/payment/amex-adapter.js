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
            }, 'amex_wallet', componentUrl);
        }
    }
});
