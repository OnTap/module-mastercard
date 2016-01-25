/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/payment/additional-validators',
        'OnTap_Tns/js/view/payment/hpf-adapter'
    ],
    function ($, ccFormComponent, additionalValidators, paymentAdapter) {
        'use strict';

        return ccFormComponent.extend({
            defaults: {
                template: 'OnTap_Tns/payment/tns-hpf',
                active: false,
                scriptLoaded: false,
                imports: {
                    onActiveChange: 'active'
                }
            },
            placeOrderHandler: null,
            validateHandler: null,

            hasSsCardType: function () {
                return false;
            },

            initObservable: function () {
                this._super()
                    .observe('active scriptLoaded');

                return this;
            },

            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },


            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },


            context: function () {
                return this;
            },


            isShowLegend: function () {
                return true;
            },


            getCode: function () {
                return 'tns_hpf';
            },


            isActive: function () {
                var active = this.getCode() === this.isChecked();

                this.active(active);

                return active;
            },


            onActiveChange: function (isActive) {
                if (isActive && !this.scriptLoaded()) {
                    this.loadScript();
                }
            },


            loadScript: function () {
                var state = this.scriptLoaded;

                $('body').trigger('processStart');
                //require([this.getUrl()], function () {
                paymentAdapter.configureApi();
                state(true);
                $('body').trigger('processStop');
                //});
            },

            getData: function () {
                return {
                    //'method': this.item.method,
                    'cardNumber': this.creditCardNumber(),
                    'cardSecurityCode': this.creditCardVerificationNumber(),
                    'cardExpiryMonth': this.creditCardExpMonth(),
                    'cardExpiryYear': this.creditCardExpYear()
                };
            },


            placeOrder: function () {
                if (this.validateHandler() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);

                    console.log('request data: %o', this.getData());
                    paymentAdapter.startSession(this.getData(), function(response){
                        console.log('scope %o', this);
                        console.log('response data %o', response);
                    });

                    this.isPlaceOrderActionAllowed(true);
                    //this._super();
                }
            }
        });
    }
);
