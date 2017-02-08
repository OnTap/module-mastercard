/*
 * Copyright (c) 2016. On Tap Networks Limited.
 */
/*global define*/
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/set-payment-information',
        'OnTap_MasterCard/js/action/check-enrolment',
        'mage/url',
        'uiLayout',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/translate',
        'Magento_Vault/js/view/payment/vault-enabler'
    ],
    function ($, ccFormComponent, additionalValidators, setPaymentInformationAction, checkEnrolmentAction, url, layout, fullScreenLoader, $t, VaultEnabler) {
        'use strict';

        return ccFormComponent.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/tns-direct',
                active: false,
                buttonTitle: null,
                buttonTitleEnabled: $t('Place Order'),
                buttonTitleDisabled: $t('Please wait...'),
                imports: {
                    onActiveChange: 'active',
                    onButtonTitleChange: 'buttonTitle'
                }
            },
            placeOrderHandler: null,
            validateHandler: null,

            initialize: function () {
                this._super();

                this.buttonTitle(this.buttonTitleEnabled);
                this.isPlaceOrderActionAllowed.subscribe($.proxy(this.buttonTitleHandler, this));

                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());

                return this;
            },

            getVaultCode: function () {
                return window.checkoutConfig.payment[this.getCode()].ccVaultCode;
            },

            buttonTitleHandler: function (isButtonEnabled) {
                if (isButtonEnabled && this.isActive()) {
                    this.buttonTitle(this.buttonTitleEnabled);
                }
            },

            initObservable: function () {
                this._super()
                    .observe('active buttonTitle');

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

            getData: function () {
                var data = this._super();
                this.vaultEnabler.visitAdditionalData(data);
                return data;
            },

            startPlaceOrder: function () {
                if (this.validateHandler() && additionalValidators.validate()) {

                    this.buttonTitle(this.buttonTitleDisabled);
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
            },

            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            }
        });
    }
);
