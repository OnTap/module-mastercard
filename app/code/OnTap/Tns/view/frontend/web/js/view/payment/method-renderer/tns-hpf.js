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
        'OnTap_Tns/js/view/payment/hpf-adapter',
        'Magento_Ui/js/modal/alert',
        'mage/translate',
        'Magento_Checkout/js/action/set-payment-information',
        'uiLayout',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, ccFormComponent, additionalValidators, paymentAdapter, alert, $t, setPaymentInformationAction, layout, fullScreenLoader) {
        'use strict';

        return ccFormComponent.extend({
            defaults: {
                template: 'OnTap_Tns/payment/tns-hpf',
                active: false,
                adapterLoaded: false,
                imports: {
                    onActiveChange: 'active'
                }
            },
            placeOrderHandler: null,
            validateHandler: null,
            sessionId: null,

            initObservable: function () {
                this._super()
                    .observe('active adapterLoaded');

                return this;
            },

            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
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

            onActiveChange: function (isActive) {
                if (isActive && !this.adapterLoaded()) {
                    this.loadAdapter();
                }
            },

            isActive: function () {
                var active = this.getCode() === this.isChecked();
                this.active(active);
                return active;
            },

            loadAdapter: function () {
                paymentAdapter.configureApi(this.getConfig());
                this.adapterLoaded(true);
            },

            errorMap: function () {
                return {
                    'cardNumber': $t('Invalid card number'),
                    'cardSecurityCode': $t('Invalid security code'),
                    'cardExpiryMonth': $t('Invalid expiry month'),
                    'cardExpiryYear': $t('Invalid expiry year')
                };
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'session': this.sessionId
                    }
                };
            },

            getCardData: function () {
                return {
                    'cardNumber': this.creditCardNumber(),
                    'cardSecurityCode': this.creditCardVerificationNumber(),
                    'cardExpiryMonth': this.creditCardExpMonth(),
                    'cardExpiryYear': this.creditCardExpYear()
                };
            },

            getConfig: function () {
                return window.checkoutConfig.payment[this.getCode()];
            },

            is3DsEnabled: function () {
                return this.getConfig()['three_d_secure'];
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

            threeDSecureCheckSuccess: function () {
                this.placeOrder();
            },

            threeDSecureCheckFailed: function () {
                fullScreenLoader.stopLoader();
            },

            threeDSecureCancelled: function () {
                fullScreenLoader.stopLoader();
                this.isPlaceOrderActionAllowed(true);
            },

            startHpfSession: function () {
                if (!this.validateHandler() || !additionalValidators.validate()) {
                    return;
                }

                this.isPlaceOrderActionAllowed(false);

                paymentAdapter.startSession(this.getCardData(), $.proxy(function(response) {
                    this.sessionId = response.session;

                    if (response.status === "fields_in_error") {
                        if (response.fieldsInError) {
                            var errors = this.errorMap();
                            for (var err in response.fieldsInError) {
                                if (!response.fieldsInError.hasOwnProperty(err)) {
                                    continue;
                                }
                                alert({
                                    content: errors[err]
                                });
                            }
                        }
                    }
                    if (response.status === "ok") {
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
                            return;
                        }
                    }
                    this.isPlaceOrderActionAllowed(true);
                }, this));
            }
        });
    }
);
