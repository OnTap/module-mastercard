/*
 * Copyright (c) 2016-2021 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/*global define*/
/*global PaymentSession*/
define(
    [
        'OnTap_MasterCard/js/view/payment/method-renderer/ach/abstract',
        'underscore',
        'jquery',
        'mage/translate'
    ],
    function (Component, _, $, $t) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/ach/hosted-session'
            },
            getFields: function () {
                return {
                    ach: {
                        accountType: "#ach-account-type",
                        bankAccountHolder: "#ach-account-holder",
                        bankAccountNumber: "#ach-account-number",
                        routingNumber: "#ach-routing-number"
                    }
                }
            },
            errorMap: function () {
                return {
                    'accountType': $t('Invalid account type'),
                    'bankAccountHolder': $t('Invalid bank account holder'),
                    'bankAccountNumber': $t('Invalid bank account number'),
                    'routingNumber': $t('Invalid routing number')
                };
            },
            load: function (callback) {
                require([this.component_url], callback);
            },
            configure: function (callback) {
                var elem = document.getElementById('ach-account-holder');
                if (elem) {
                    this._configure(callback);
                } else {
                    setTimeout(this.configure.bind(this, callback), 100);
                }
            },
            _configure: function (callback) {
                PaymentSession.configure({
                    fields: this.getFields(),
                    frameEmbeddingMitigation: ['x-frame-options'],
                    callbacks: {
                        initialized: callback,
                        formSessionUpdate: this.formSessionUpdate.bind(this)
                    }
                }, this.getId());
            },
            formSessionUpdate: function (response) {
                var fields = this.getFields();
                if (response.status === "fields_in_error") {
                    if (response.errors) {
                        var errors = this.errorMap();
                        _.keys(response.errors).forEach(function(errorField) {
                            var message = errors[errorField],
                                elem_id = fields.ach[errorField] + '-error';

                            $(elem_id).text(message).show();
                        });
                        this.sessionUpdateErrorCallback(response);
                    }
                }
                if (response.status === "ok") {
                    this.sessionUpdateCallback(response);
                }
            },
            pay: function (callback, errorCallback) {
                var fields = this.getFields();
                _.values(fields.ach).forEach(function (field) {
                    $(field + '-error').hide();
                });

                this.sessionUpdateErrorCallback = errorCallback;
                this.sessionUpdateCallback = callback;
                PaymentSession.updateSessionFromForm('ach', undefined, this.getId());
            }
        });
    }
);
