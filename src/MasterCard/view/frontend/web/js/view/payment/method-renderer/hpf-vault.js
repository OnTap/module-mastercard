/*
 * Copyright (c) 2016. On Tap Networks Limited.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'mage/translate',
    'Magento_Ui/js/modal/alert'
], function ($, VaultComponent, $t, alert) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'OnTap_MasterCard/payment/hpf-vault',
            active: false,
            isConfigured: false,
            session: {},
            imports: {
                onActiveChange: 'active'
            }
        },

        initObservable: function () {
            this._super()
                .observe([
                    'active',
                    'isConfigured',
                    'session',
                    'useCcv'
                ]);
            return this;
        },

        /**
         * @returns {String}
         */
        getId: function () {
            return 'tns_hpf_vault_' + this.index;
        },

        /**
         * @returns {String}
         */
        getMaskedCard: function () {
            return this.details.cc_number;
        },

        /**
         * Get expiration date
         * @returns {String}
         */
        getExpirationDate: function () {
            return this.details.cc_expr_month + '/' + this.details.cc_expr_year;
        },

        /**
         * Get card type
         * @returns {String}
         */
        getCardType: function () {
            return this.details.type;
        },

        /**
         * @returns {String}
         */
        getToken: function () {
            return this.publicHash;
        },

        getCvvImageHtml: function() {
            return '<img src="' + this.getCvvImageUrl()
                + '" alt="' + $t('Card Verification Number Visual Reference')
                + '" title="' + $t('Card Verification Number Visual Reference')
                + '" />';
        },

        getCvvImageUrl: function() {
            return window.checkoutConfig.payment.ccform.cvvImageUrl[this.getCode()];
        },

        onActiveChange: function (isActive) {
            if (isActive && this.useCcv()) {
                this.loadAdapter();
            }
        },

        errorMap: function () {
            return {
                'securityCode': $t('Invalid security code'),
            };
        },

        paymentAdapterLoaded: function () {
            PaymentSession.configure({
                fields: {
                    card: {
                        securityCode: '#' + this.getId() + '_cvv',
                    }
                },
                frameEmbeddingMitigation: ['x-frame-options'],
                callbacks: {
                    initialized: function () {
                        this.isConfigured(true);
                        this.isPlaceOrderActionAllowed(true);
                    }.bind(this),
                    formSessionUpdate: function (response) {
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
                                this.isPlaceOrderActionAllowed(true);
                            }
                        }
                        if (response.status === "ok") {
                            this.session(response.session);
                            this.placeOrder();
                        }
                    }.bind(this)
                },
                interaction: {
                    displayControl: {
                        formatCard: "EMBOSSED",
                        invalidFieldCharacters: "REJECT"
                    }
                }
            }, this.getId());
        },

        loadAdapter: function () {
            if (this.isConfigured()) {
                return;
            }
            this.isPlaceOrderActionAllowed(false);
            require([this.component_url], this.paymentAdapterLoaded.bind(this));
        },

        isActive: function () {
            var active = this.getId() === this.isChecked();
            this.active(active);
            return active;
        },

        savePayment: function () {
            if (this.useCcv()) {
                this.isPlaceOrderActionAllowed(false);
                PaymentSession.updateSessionFromForm('card', undefined, this.getId());
                return this;
            } else {
                this.placeOrder();
            }
        },

        getData: function () {
            var data = this._super();

            var session = this.session();
            data['additional_data']['session'] = session['id'];

            return data;
        }
    });
});
