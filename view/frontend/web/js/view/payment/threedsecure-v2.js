/*
 * Copyright (c) 2016-2020 Mastercard
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
    'ko',
    'jquery',
    'uiComponent',
    'mage/url',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/full-screen-loader',
    'OnTap_MasterCard/js/action/authenticate-payer',
    'OnTap_MasterCard/js/action/initiate-authentication'
], function (
    ko,
    $,
    Component,
    url,
    modal,
    fullScreenLoader,
    authenticatePayerAction,
    initiateAuthenticationAction
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'OnTap_MasterCard/threedsecure/treedsecure-v2'
        },
        onComplete: null,
        onError: null,
        onCancel: null,
        iframe: null,
        modal: null,
        acsComplete: false,
        id: null,
        messageContainer: null,
        isPlaceOrderActionAllowed: null,

        initialize: function (config) {
            this._super();

            this.onComplete = config.onComplete;
            this.onError = config.onError;
            this.onCancel = config.onCancel;
            this.id = config.id;
            this.messageContainer = config.messages;
            this.isPlaceOrderActionAllowed = config.isPlaceOrderActionAllowed;

            return this;
        },

        getId: function () {
            return this.id;
        },

        isVisible: function () {
            return false;
        },

        threeDSecureV2Start: function () {

            this.acsComplete = false;
            fullScreenLoader.startLoader();
            this.isPlaceOrderActionAllowed(false);
            initiateAuthenticationAction.execute(this.messageContainer)
                .done($.proxy(this.onInitiateAuthentication, this))
                .fail($.proxy(this.onError, this));
        },

        /**
         * @param res {object}
         * @param res.html {String}
         * @returns {jQuery.Deferred}
         */
        onInitiateAuthentication: function (res) {
            $('#' + this.getId() + '_threedsecure_v2_container').html(res.html);
            eval($('#initiate-authentication-script').text());

            return $.when(
                authenticatePayerAction.execute({
                    browserDetails: {
                        javaEnabled: navigator.javaEnabled(),
                        language: navigator.language,
                        screenHeight: window.screen.height,
                        screenWidth: window.screen.width,
                        timeZone: new Date().getTimezoneOffset(),
                        colorDepth: screen.colorDepth,
                        acceptHeaders: 'application/json',
                        '3DSecureChallengeWindowSize': 'FULL_SCREEN'
                    }
                }, this.messageContainer)
            ).done($.proxy(this.onAuthenticatePayer, this))
            .fail($.proxy(this.onError, this));
        },

        /**
         * @param res {object}
         * @param res.html {String}
         * @param res.action {String}
         */
        onAuthenticatePayer: function (res) {
            window.treeDS2Completed = $.proxy(this.modalCompleted, this);

            this.modal = $('#' + this.getId() + '_threedsecure_v2_modal');

            modal({
                type: 'slide',
                title: $.mage.__('Process Secure Payment'),
                buttons: [],
                closed: $.proxy(this.onModalClose, this),
                clickableOverlay: false
            }, this.modal);

            this.modal.html(res.html);
            eval($('#authenticate-payer-script').text());

            this.iframe = $('iframe', this.modal);

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

            this.iframe.parent().css({
                height: '100%'
            });

            if (res.action === 'challenge') {
                this.modal.modal('openModal');
                this.iframe.on('load', function () {
                    fullScreenLoader.stopLoader();
                });
            }
        },

        modalCompleted: function () {
            fullScreenLoader.stopLoader();
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
