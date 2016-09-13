/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
