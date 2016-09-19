/*
 * Copyright (c) 2016. On Tap Networks Limited.
 */

define([
    'ko',
    'jquery',
    'uiComponent',
    'mage/url',
    'OnTap_MasterCard/js/action/check-enrolment',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/full-screen-loader'
], function (ko, $, Component, url, checkEnrolmentAction, modal, fullScreenLoader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'OnTap_MasterCard/threedsecure/iframe',
            iframeSelector: '[data-role=tns-threedsecure-iframe]'
        },
        onComplete: null,
        onError: null,
        onCancel: null,
        iframe: null,
        modal: null,
        iframeLoaded: false,
        acsComplete: false,
        id: null,
        messageContainer: null,
        checkUrl: null,

        initialize: function(config) {
            this._super()
                .initObservable();

            this.onComplete = config.onComplete;
            this.onError = config.onError;
            this.onCancel = config.onCancel;
            this.id = config.id;
            this.messageContainer = config.messages;

            return this;
        },

        getId: function () {
            return this.id;
        },

        isVisible: function () {
            return false;
        },

        threeDSecureOpen: function () {
            this.acsComplete = false;
            fullScreenLoader.startLoader();

            window.tnsThreeDSecureClose = $.proxy(this.iframeFormCompleted, this);

            if (this.iframeLoaded !== true) {
                this.modal = $('#' + this.getId() + '_threedsecure_modal');
                this.iframe = $(this.iframeSelector, this.modal);

                modal({
                    type: 'slide',
                    title: $.mage.__('Process Secure Payment'),
                    buttons: [],
                    closed: $.proxy(this.onModalClose, this),
                    clickableOverlay: false
                }, this.modal);

                this.iframe.on('load', $.proxy(function () {
                    this.modal.css({
                        height: '100%'
                    });
                    this.modal.parent().css({
                        height: '80%'
                    });
                    this.iframe.css({
                        height: '100%',
                        width: '100%'
                    });
                    this.iframeLoaded = true;

                    fullScreenLoader.stopLoader();

                }, this));
            }

            $.when(checkEnrolmentAction(this.messageContainer, this.checkUrl)).fail(
                $.proxy(this.onError, this)
            ).done(
                $.proxy(this.isEnrolled, this)
            );
        },

        isEnrolled: function (response) {
            if (response.result == "CARD_ENROLLED") {
                // Card is enrolled, proceed with ACS
                this.iframe.attr('src', url.build('tns/threedsecure/form'));
                this.modal.modal('openModal');
            } else {
                // Card is not enrolled or error
                fullScreenLoader.stopLoader();
                this.iframeFormCompleted();
            }
        },

        iframeFormCompleted: function () {
            this.acsComplete = true;
            this.modal.modal('closeModal');
            this.onComplete();
        },

        onModalClose: function () {
            if (!this.acsComplete) {
                this.onCancel();
            }
        }
    });
});