/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
/*jshint jquery:true browser:true*/
require([
    'jquery',
    'mage/backend/validation'
], function ($) {
    'use strict';
    $.validator.addMethod('validate-json', function (value) {
        try {
            JSON.parse(value);
        } catch (err) {
            return false;
        }
        return true
    }, 'Invalid JSON string.');
});
