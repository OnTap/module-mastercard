define([
    'uiComponent',
    'uiLayout',
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data'
], function (Component, layout, $, alert, $t, setShippingInformationAction, placeOrderAction, redirectOnSuccessAction, quote, checkoutData) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();

            var threeDSecureComponent = {
                parent: this.name,
                name: this.name + '.threedsecure',
                displayArea: 'threedsecure',
                component: 'OnTap_MasterCard/js/view/payment/threedsecure',
                config: {
                    id: this.method,
                    checkUrl: this.check_url,
                    onComplete: $.proxy(this.redirectPlaceOrder, this),
                    onError: $.proxy(this.threeDSecureCheckFailed, this),
                    onCancel: $.proxy(this.threeDSecureCancelled, this)
                }
            };
            layout([threeDSecureComponent]);

            return this;
        },

        redirectPlaceOrder: function () {
            this.placeOrder();
        },

        threeDSecureCheckFailed: function () {
            $('body').trigger('processStop');
            alert({
                content: $t('Failed to process 3D-Secure')
            });
        },

        threeDSecureCancelled: function () {

        },

        placeOrderUi: function () {
            if (!window.isCustomerLoggedIn) {
                quote.guestEmail = this.email;
                checkoutData.setValidatedEmailValue(this.email);
            }

            if (!quote.shippingMethod()) {
                alert({
                    content: $t('Please select a shipping method.')
                });
                return;
            }

            var method = quote.shippingMethod()['method_code'],
                carrier = quote.shippingMethod()['carrier_code'];

            if (!method || !carrier) {
                alert({
                    content: $t('Please select a shipping method.')
                });
                return;
            }

            $('body').trigger('processStart');

            var action = setShippingInformationAction();
            $.when(action).done($.proxy(function () {
                if (this.three_d_secure) {
                    $('body').trigger('processStop');
                    this.delegate('threeDSecureOpen', this);
                } else {
                    this.placeOrder();
                }
            }, this)).fail(function () {
                $('body').trigger('processStop');
                alert({
                    content: $t('Failed saving shipping address, please try again later.')
                });
            });
        },

        placeOrder: function () {
            $('body').trigger('processStart');

            var action = placeOrderAction({
                method: this.method
            });

            $.when(action).done(function () {
                redirectOnSuccessAction.execute();
            }).fail(function () {
                $('body').trigger('processStop');
                alert({
                    content: $t('Payment could not be completed, please try again later.')
                });
            });
        }
    });
});
