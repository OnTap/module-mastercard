/*
 * Copyright (c) 2016-2019 Mastercard
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
/*browser:true*/
/*global define*/
define([
    'jquery'
], function ($) {
    'use strict';

    return {
        loadApi: function (componentUrl, onLoadedCallback, onError, onCancel, onComplete) {
            window.tnsErrorCallback = $.proxy(onError, this);
            window.tnsCancelCallback = $.proxy(onCancel, this);
            window.tnsCompletedCallback = $.proxy(onComplete, this);

            var node = requirejs.load({
                contextName: '_',
                onScriptLoad: $.proxy(onLoadedCallback, this)
            }, 'tns_hosted', componentUrl);

            node.setAttribute('data-error', 'window.tnsErrorCallback');
            node.setAttribute('data-cancel', 'window.tnsCancelCallback');
            node.setAttribute('data-complete', 'window.tnsCompletedCallback');
        },
        safeNumber: function (num) {
            return parseFloat(num).toFixed(2);
        },
        configureApi: function (merchant, quote, sessionId, sessionVersion) {
            var totals = quote.totals();
            Checkout.configure({
                merchant: merchant,
                order: {
                    amount: this.safeNumber(totals.base_grand_total),
                    currency: totals.quote_currency_code,
                    description: 'Ordered items'
                },
                interaction: {
                    merchant: {
                        name: 'Magento'
                    },
                    displayControl: {
                        customerEmail: 'HIDE',
                        billingAddress: 'HIDE',
                        orderSummary: 'HIDE',
                        paymentTerms: 'HIDE',
                        shipping: 'HIDE'
                    }
                },
                session: {
                    id: sessionId,
                    version: sessionVersion
                }
            });
        },
        showPayment: function () {
            Checkout.showLightbox();
        }
    };
});
