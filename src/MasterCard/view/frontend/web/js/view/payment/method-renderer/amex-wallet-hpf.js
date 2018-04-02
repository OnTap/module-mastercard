/*
 * Copyright (c) 2017. On Tap Networks Limited.
 */
/*global define*/
define(
    [
        'OnTap_MasterCard/js/view/payment/method-renderer/base-adapter',
        'OnTap_MasterCard/js/view/payment/hpf-adapter',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/full-screen-loader',
        'uiLayout',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/redirect-on-success'
    ],
    function (
        Component,
        paymentAdapter,
        $,
        quote,
        setPaymentInformationAction,
        globalMessageList,
        loader,
        layout,
        additionalValidators,
        placeOrderAction,
        redirectOnSuccessAction
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'OnTap_MasterCard/payment/amex_wallet'
            },
            additionalData: {},
            adapter: null,
            inPayment: false,

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
                        onComplete: $.proxy(this.redirectPlaceOrder, this),
                        onError: $.proxy(this.threeDSecureCheckFailed, this),
                        onCancel: $.proxy(this.threeDSecureCancelled, this)
                    }
                };
                layout([threeDSecureComponent]);

                return this;
            },

            threeDSecureCheckFailed: function () {
                loader.stopLoader();
            },

            threeDSecureCancelled: function () {
                this.isPlaceOrderActionAllowed(true);
            },

            is3DsEnabled: function () {
                return this.getConfig().three_d_secure;
            },

            loadAdapter: function () {
                var config = this.getConfig();
                var totals = quote.totals();

                var data = {
                    wallets: {
                        amexExpressCheckout: {
                            enabled: true,
                            initTags: {
                                theme: 'responsive',
                                env: config.env,
                                disable_btn: 'false',
                                client_id: config.client_id
                            }
                        }
                    },
                    order: {
                        amount: this.safeNumber(totals.base_grand_total),
                        currency: totals.quote_currency_code
                    },
                    callbacks: {
                        amexExpressCheckout: $.proxy(this.aecInteractionFinished, this)
                    }
                };

                var aecButton = $('#amex-express-checkout').get(0);

                new MutationObserver($.proxy(this.adapterLoaded, this, true))
                    .observe(aecButton, { childList: true });

                aecButton.addEventListener('click', $.proxy(function (e) {
                    if (!additionalValidators.validate()) {
                        e.stopImmediatePropagation();
                        e.stopPropagation();
                    }
                }, this), true);

                paymentAdapter.init(
                    this.getCode(),
                    config.component_url,
                    data,
                    $.proxy(this.paymentAdapterLoaded, this),
                    config.debug
                );
            },

            paymentAdapterLoaded: function (adapter, response) {
                this.adapter = adapter;
                if (response.status === 'system_error') {
                    this.messageContainer.addErrorMessage({message: response.message});
                }
            },

            getConfig: function () {
                return window.checkoutConfig.wallets[this.getCode()];
            },

            paymentFailed: function () {
                loader.stopLoader();
            },

            placeOrder: function () {
                loader.stopLoader();
                if (this.is3DsEnabled()) {
                    this.delegate('threeDSecureOpen', this);
                } else {
                    this.redirectPlaceOrder();
                }
            },

            redirectPlaceOrder: function () {
                loader.startLoader();
                $.when(placeOrderAction(this.getData(), this.messageContainer))
                    .done(function () {
                        redirectOnSuccessAction.execute();
                    })
                    .fail(function () {
                        loader.stopLoader();
                    });
            },

            getData: function (session) {
                if (session !== undefined) {
                    var _session = JSON.stringify(session);
                    return $.extend(this._super(), {
                        additional_data: {
                            session: _session
                        }
                    });
                }
                return this._super();
            },

            aecInteractionFinished: function (response) {
                if (response.status !== 'ok' || !response.session) {
                    this.messageContainer.addErrorMessage({message: 'Unexpected AEC status, please try again later.'});
                } else {

                    loader.startLoader();

                    var xhr = setPaymentInformationAction(this.messageContainer, this.getData(response.session));
                    $.when(xhr).done($.proxy(function () {
                        this.placeOrder();
                    }, this)).fail(
                        $.proxy(this.paymentFailed, this)
                    );
                }
            }
        });
    }
);
