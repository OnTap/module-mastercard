/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mage/url'

], function ($, _, url) {
    'use strict';

    $.widget('tns.threedsecure', {
        options: {
            frameSelector: 'iframe',
            context: null
        },
        iframe: null,
        iframeLoaded: false,

        _create: function () {
            this.iframe = this.element.children(this.options.frameSelector);
            this.iframe.hide();
            this.options.context.setModalOpenCallback($.proxy(this.loadForm, this));
        },

        loadForm: function () {
            if (this.iframeLoaded === true) {
                return;
            }
            this.iframe.attr('src', url.build('tns/threedsecure/form'));
            this.iframe.load($.proxy(function () {
                this.element.css({
                    height: '100%'
                });
                this.element.parent().css({
                    height: '80%'
                });
                this.iframe.css({
                    height: '100%',
                    width: '100%'
                });
                this.iframe.show();
                this.iframeLoaded = true;
                this._trigger('formLoaded');
            }, this));
        }
    });

    return $.tns.threedsecure;
});