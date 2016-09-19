/*
 * Copyright (c) 2016. On Tap Networks Limited.
 */
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'OnTap_MasterCard/js/view/payment/hpf-adapter',
        'Magento_Ui/js/modal/alert',
        'mage/translate',
        'Magento_Checkout/js/action/set-payment-information',
        'uiLayout',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Vault/js/view/payment/vault-enabler'
    ],
    function ($, ccFormComponent, additionalValidators, paymentAdapter, alert, $t, setPaymentInformationAction, layout, fullScreenLoader, VaultEnabler) {
        'use strict';

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
            inPayment: false,

            initialize: function () {
                this._super();
                this.vaultEnabler = VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());

                return this;
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
                        this.inPayment = false;
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

                paymentAdapter.loadApi(
                    this.getCardFields(),
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
                    cardNumber: "#tns_hpf_cc_number",
                    expiryMonth: "#tns_hpf_expiration",
                    expiryYear: "#tns_hpf_expiration_yr",
                    securityCode: "#tns_hpf_cc_cid"
                }
            },

            getConfig: function () {
                return window.checkoutConfig.payment[this.getCode()];
            },

            is3DsEnabled: function () {
                return this.getConfig()['three_d_secure'];
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
                layout([threeDSecureComponent]);

                return this;
            },

            threeDSecureCheckSuccess: function () {
                this.placeOrder();
            },

            threeDSecureCheckFailed: function () {
                fullScreenLoader.stopLoader();
                this.isPlaceOrderActionAllowed(true);
            },

            threeDSecureCancelled: function () {
                this.isPlaceOrderActionAllowed(true);
            },

            startHpfSession: function () {
                this.isPlaceOrderActionAllowed(false);
                this.buttonTitle(this.buttonTitleDisabled);
                this.inPayment = false;

                paymentAdapter.startSession($.proxy(function(response) {
                    if (this.inPayment === true) {
                        console.info("Duplicate response from session.updateSessionFromForm");
                        return;
                    }
                    this.inPayment = true;

                    if (response.status === "fields_in_error") {
                        if (response.errors) {
                            var errors = this.errorMap(),
                                message = "";
                            for (var err in response.errors) {
                                if (!response.errors.hasOwnProperty(err)) {
                                    continue;
                                }
                                message += '<p>' + errors[err] + '</p>';
                            }
                            alert({
                                content: message,
                                closed: $.proxy(function () {
                                    this.isPlaceOrderActionAllowed(true);
                                }, this)
                            });
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
                            this.placeOrder();
                        }
                    }
                }, this));
            }
        });
    }
);
