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
        'Magento_Checkout/js/action/set-payment-information',
        'OnTap_Tns/js/action/check-enrolment',
        'mage/url',
        'uiLayout',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, ccFormComponent, additionalValidators, setPaymentInformationAction, checkEnrolmentAction, url, layout, fullScreenLoader) {
        'use strict';

        return ccFormComponent.extend({
            defaults: {
                template: 'OnTap_Tns/payment/tns-direct',
                active: false,
                imports: {
                    onActiveChange: 'active'
                }
            },
            placeOrderHandler: null,
            validateHandler: null,

            initObservable: function () {
                this._super()
                    .observe('active');

                return this;
            },

            is3DsEnabled: function () {
                return this.getConfig()['three_d_secure'];
            },

            getConfig: function () {
                return window.checkoutConfig.payment[this.getCode()];
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
                return 'tns_direct';
            },

            isActive: function () {
                var active = this.getCode() === this.isChecked();

                this.active(active);

                return active;
            },

            initChildren: function () {
                this._super();

                var threeDSecureComponent = {
                    parent: this.name,
                    name: this.name + '.threedsecure',
                    displayArea: 'threedsecure',
                    component: 'OnTap_Tns/js/view/payment/threedsecure',
                    config: {
                        id: this.item.method,
                        messages: this.messageContainer,
                        onComplete: $.proxy(this.threeDSecureCheckSuccess, this),
                        onError: $.proxy(this.threeDSecureCheckFailed, this),
                        onCancel: $.proxy(this.threeDSecureCancelled, this)
                    }
                };
                layout([threeDSecureComponent]);

                return this;
            },

            threeDSecureCancelled: function () {
                fullScreenLoader.stopLoader();
                this.isPlaceOrderActionAllowed(true);
            },

            threeDSecureCheckSuccess: function () {
                this.placeOrder();
            },

            threeDSecureCheckFailed: function () {
                fullScreenLoader.stopLoader();
            },

            startPlaceOrder: function () {
                if (this.validateHandler() && additionalValidators.validate()) {

                    this.isPlaceOrderActionAllowed(false);

                    if (this.is3DsEnabled()) {
                        fullScreenLoader.startLoader();

                        var action = setPaymentInformationAction(this.messageContainer, this.getData());

                        $.when(action).done($.proxy(function() {
                            fullScreenLoader.stopLoader();
                            this.delegate('threeDSecureOpen', this);
                        }, this)).fail(
                            $.proxy(this.threeDSecureCheckFailed, this)
                        );
                    } else {
                        this.placeOrder();
                    }
                }
            }
        });
    }
);
