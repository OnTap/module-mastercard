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
define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/modal'
], function ($, Component, modal) {
    'use strict';

    return Component.extend({
        defaults: {
            detailsButton: '[data-role=tns-full-payment-details-button]',
            contentContainer: '[data-role=tns-full-payment-details-content]'
        },
        initialize: function () {
            this._super()
                .initObservable();

            $(this.detailsButton).on('click', $.proxy(this.openDetails, this));

            this.content = $(this.contentContainer);
            modal({
                title: $.mage.__('Payment')
            }, this.content);

            return this;
        },
        openDetails: function () {
            this.content.modal('openModal');
        }
    });
});
