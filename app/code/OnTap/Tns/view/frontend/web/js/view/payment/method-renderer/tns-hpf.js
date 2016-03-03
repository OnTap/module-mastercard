/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
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
                },
                creditCardExpYear: '',
                creditCardExpMonth: ''
            },
            placeOrderHandler: null,
            validateHandler: null,
            sessionId: null,

            initObservable: function () {
                this._super()
                    .observe([
                        'active',
                        'adapterLoaded',
                        'creditCardExpYear',
                        'creditCardExpMonth'
                    ]);

                return this;
            },

            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            getCvvImageHtml: function() {
                return '<img src="' + this.getCvvImageUrl()
                    + '" alt="' + $t('Card Verification Number Visual Reference')
                    + '" title="' + $t('Card Verification Number Visual Reference')
                    + '" />';
            },

            getCcMonthsValues: function() {
                return _.map(this.getCcMonths(), function(value, key) {
                    return {
                        'value': key,
                        'month': value
                    }
                });
            },

            getCcYearsValues: function() {
                return _.map(this.getCcYears(), function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },

            getCcMonths: function() {
                return window.checkoutConfig.payment.ccform.months[this.getCode()];
            },

            getCcYears: function() {
                return window.checkoutConfig.payment.ccform.years[this.getCode()];
            },

            getCvvImageUrl: function() {
                return window.checkoutConfig.payment.ccform.cvvImageUrl[this.getCode()];
            },

            context: function () {
                return this;
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
                var config = this.getConfig();

                paymentAdapter.loadApi(
                    config.component_url,
                    config.merchant_username,
                    $.proxy(this.paymentAdapterLoaded, this),
                    config.debug
                );
            },

            isCheckoutDisabled: function () {
                return !this.adapterLoaded() || !this.isPlaceOrderActionAllowed();
            },

            paymentAdapterLoaded: function (adapter) {
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
                //if (!this.validateHandler() || !additionalValidators.validate()) {
                //    return;
                //}
                //
                //this.isPlaceOrderActionAllowed(false);

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
