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
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/translate',
        'Magento_Checkout/js/action/set-payment-information',
        'uiLayout',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Vault/js/view/payment/vault-enabler',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/action/redirect-on-success',
        'OnTap_MasterCard/js/action/cancel-order',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Ui/js/model/messages'
    ],
    function (
        $,
        ccFormComponent,
        additionalValidators,
        $t,
        setPaymentInformationAction,
        layout,
        fullScreenLoader,
        VaultEnabler,
        modal,
        redirectOnSuccessAction,
        cancelOrder,
        customerData,
        setShippingInformationAction,
        errorProcessor,
        Message
    ) {
        'use strict';

        var orderId = null;
        return ccFormComponent.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/tns-hpf',
                active: false,
                adapterLoaded: false,
                buttonTitle: null,
                buttonTitleEnabled: $t('Place Order'),
                buttonTitleDisabled: $t('Please wait...'),
                imports: {
                    onActiveChange: 'active'
                },
                creditCardExpYear: '',
                creditCardExpMonth: ''
            },
            placeOrderHandler: null,
            validateHandler: null,
            sessionId: null,

            initialize: function () {
                this._super();
                this.vaultEnabler = VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());
                this.redirectAfterPlaceOrder = !this.is3Ds2Enabled();

                return this;
            },

            /**
             * @return {*}
             */
            getPlaceOrderDeferredObject: function () {
                return this._super().done(function (res) {
                    orderId = res;
                    return res;
                });
            },

            afterPlaceOrder: function () {
                if (!this.is3Ds2Enabled()) {
                    return;
                }
                this.isPlaceOrderActionAllowed(false);
                this.delegate('threeDSecureV2Open', this, orderId);
            },

            getId: function () {
                return this.index;
            },

            getVaultCode: function () {
                return window.checkoutConfig.payment[this.getCode()].ccVaultCode;
            },

            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'active',
                        'adapterLoaded',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'buttonTitle'
                    ]);

                this.buttonTitle(this.buttonTitleDisabled);
                this.isPlaceOrderActionAllowed.subscribe(function (allowed) {
                    if (allowed === true && this.isActive()) {
                        this.buttonTitle(this.buttonTitleEnabled);
                    }
                }, this);
                this.adapterLoaded.subscribe($.proxy(function (loaded) {
                    if (loaded === true && this.isActive()) {
                        this.buttonTitle(this.buttonTitleEnabled);
                    }
                }, this));

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

            hasVerification: function() {
                return window.checkoutConfig.payment.ccform.hasVerification[this.getCode()];
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
                require([config.component_url], this.paymentAdapterLoaded.bind(this));
            },

            isCheckoutDisabled: function () {
                return !this.adapterLoaded() || !this.isPlaceOrderActionAllowed();
            },

            paymentAdapterLoaded: function () {
                this.isPlaceOrderActionAllowed(false);
                this.buttonTitle(this.buttonTitleDisabled);

                PaymentSession.configure({
                    fields: this.getCardFields(),
                    frameEmbeddingMitigation: ['x-frame-options'],
                    callbacks: {
                        initialized: function () {
                            this.adapterLoaded(true);
                            this.isPlaceOrderActionAllowed(true);
                        }.bind(this),
                        formSessionUpdate: this.formSessionUpdate.bind(this)
                    }
                }, this.getId());
            },

            formSessionUpdate: function (response) {
                var fields = this.getCardFields();
                for (var field in fields.card) {
                    if (!fields.card.hasOwnProperty(field)) {
                        continue;
                    }
                    $(fields.card[field] + '-error').hide();
                }

                if (response.status === "fields_in_error") {
                    if (response.errors) {
                        var errors = this.errorMap();
                        for (var err in response.errors) {
                            if (!response.errors.hasOwnProperty(err)) {
                                continue;
                            }
                            var message = errors[err],
                                elem_id = fields.card[err] + '-error';

                            $(elem_id).text(message).show();
                        }
                        fullScreenLoader.stopLoader();
                    }
                }
                if (response.status === "ok") {
                    this.sessionId = response.session.id;
                    if (this.is3DsEnabled()) {
                        var action = setPaymentInformationAction(this.messageContainer, this.getData());

                        $.when(action).done($.proxy(function() {
                            this.delegate('threeDSecureOpen', this);
                        }, this)).fail(
                            $.proxy(this.threeDSecureCheckFailed, this)
                        );
                    } else {
                        this.isPlaceOrderActionAllowed(true);
                        this.placeOrder();
                    }
                }
            },

            savePayment: function () {
                PaymentSession.updateSessionFromForm('card', undefined, this.getId());
            },

            errorMap: function () {
                return {
                    'cardNumber': $t('Invalid card number'),
                    'securityCode': $t('Invalid security code'),
                    'expiryMonth': $t('Invalid expiry month'),
                    'expiryYear': $t('Invalid expiry year')
                };
            },

            getData: function () {
                var data = {
                    'method': this.item.method,
                    'additional_data': {
                        'session': this.sessionId
                    }
                };
                this.vaultEnabler.visitAdditionalData(data);
                return data;
            },

            getCardFields: function () {
                return {
                    card: {
                        cardNumber: "#tns_hpf_cc_number",
                        number: "#tns_hpf_cc_number",
                        expiryMonth: "#tns_hpf_expiration",
                        expiryYear: "#tns_hpf_expiration_yr",
                        securityCode: "#tns_hpf_cc_cid"
                    }
                }
            },

            getConfig: function () {
                return window.checkoutConfig.payment[this.getCode()];
            },

            is3DsEnabled: function () {
                return this.getConfig()['three_d_secure'] && this.getConfig()['three_d_secure_version'] === 1;
            },

            is3Ds2Enabled: function() {
                return this.getConfig()['three_d_secure'] && this.getConfig()['three_d_secure_version'] === 2;
            },

            initChildren: function () {
                this._super();
                var config = this.getConfig();

                var threeDSecureComponent = {
                    parent: this.name,
                    name: this.name + '.threedsecure',
                    displayArea: 'threedsecure',
                    component: 'OnTap_MasterCard/js/view/payment/threedsecure',
                    config: {
                        id: this.item.method,
                        messages: this.messageContainer,
                        checkUrl: config.check_url,
                        onComplete: $.proxy(this.threeDSecureCheckSuccess, this),
                        onError: $.proxy(this.threeDSecureCheckFailed, this),
                        onCancel: $.proxy(this.threeDSecureCancelled, this)
                    }
                };

                var threeDSecureV2Component = {
                    parent: this.name,
                    name: this.name + '.threedsecureV2',
                    displayArea: 'threedsecure',
                    component: 'OnTap_MasterCard/js/view/payment/threedsecure-v2',
                    config: {
                        id: this.item.method,
                        messages: this.messageContainer,
                        onComplete: $.proxy(this.threeDSecureV2CheckSuccess, this),
                        onError: $.proxy(this.threeDSecureV2CheckFailed, this),
                        onCancel: $.proxy(this.threeDSecureV2Cancelled, this),
                        isPlaceOrderActionAllowed: $.proxy(this.isPlaceOrderActionAllowed, this)
                    }
                };
                layout([threeDSecureComponent, threeDSecureV2Component]);

                return this;
            },

            threeDSecureCheckSuccess: function () {
                this.isPlaceOrderActionAllowed(true);
                this.placeOrder();
            },

            threeDSecureCheckFailed: function () {
                console.error('3DS check failed', arguments);
                fullScreenLoader.stopLoader();
                this.isPlaceOrderActionAllowed(true);
            },

            threeDSecureCancelled: function () {
                this.isPlaceOrderActionAllowed(true);
            },

            threeDSecureV2CheckSuccess: function () {
                redirectOnSuccessAction.execute();
            },

            threeDSecureV2CheckFailed: function () {
                console.error('3DS check failed', arguments);
                this.threeDSecureCancelled();
                errorProcessor.process({
                    message: $t('Transaction declined by 3D-Secure validation.')
                }, this.messageContainer)
            },

            threeDSecureV2Cancelled: function () {
                // silence cancel action
                return cancelOrder.execute(orderId, new Message()).done(function () {
                    return customerData.reload(
                        ['cart', 'checkout-data', 'messages'],
                        false
                    );
                }).done(function () {
                    return setShippingInformationAction();
                }).done($.proxy(function () {
                    this.isPlaceOrderActionAllowed(true);
                    fullScreenLoader.stopLoader();
                }, this));
            }
        });
    }
);
