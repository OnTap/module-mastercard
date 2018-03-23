define([
    'Magento_Ui/js/form/form',
    'jquery',
    'uiRegistry',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/shipping-address/form-popup-state'
], function (Component, $, registry, customer, quote, checkoutData, createShippingAddress, selectShippingAddress, checkoutDataResolver, modal, formPopUpState) {
    'use strict';
    return Component.extend({
        isFormPopUpVisible: formPopUpState.isVisible,
        modalInstance: null,
        temporaryAddress: {},

        initialize: function () {
            this._super();

            // xxx: data consistency hack
            if (typeof this.shippingFromWallet.region === 'object') {
                this.shippingFromWallet.region = this.shippingFromWallet.region.region;
            }

            registry.async('checkoutProvider')($.proxy(function (checkoutProvider) {
                var shippingAddressData = createShippingAddress(this.shippingFromWallet);
                // var shippingAddressData = checkoutData.getShippingAddressFromData();

                if (shippingAddressData) {
                    checkoutProvider.set(
                        'shippingAddress',
                        $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                    );
                }
                checkoutProvider.on('shippingAddress', function (shippingAddrsData) {
                    checkoutData.setShippingAddressFromData(shippingAddrsData);
                });

                checkoutData.setSelectedShippingAddress(shippingAddressData.getKey());

                checkoutDataResolver.resolveShippingAddress();
            }, this));

            this.isFormPopUpVisible.subscribe($.proxy(function (value) {
                if (value) {
                    this.getPopUp().openModal();
                }
            }, this));

            return this;
        },

        onPopupSave: function () {
            this.source.set('params.invalid', false);
            this.source.trigger('shippingAddress.data.validate');
            if (!this.source.get('params.invalid')) {
                var addressData = this.source.get('shippingAddress');

                var newShippingAddress = createShippingAddress(addressData);
                selectShippingAddress(newShippingAddress);
                checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                checkoutData.setNewCustomerShippingAddress($.extend(true, {}, addressData));
                this.getPopUp().closeModal();
            }
        },

        onPopupCancel: function () {
            checkoutData.setShippingAddressFromData($.extend(true, {}, this.temporaryAddress));
            this.getPopUp().closeModal();
        },

        getPopUp: function () {
            if (this.modalInstance) {
                return this.modalInstance;
            }

            var buttons = this.popUpForm.options.buttons;

            this.popUpForm.options.buttons = [
                {
                    text: buttons.save.text ? buttons.save.text : $t('Save Address'),
                    class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                    click: $.proxy(this.onPopupSave, this)
                },
                {
                    text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                    class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',

                    /** @inheritdoc */
                    click: $.proxy(this.onPopupCancel, this)
                }
            ];

            this.popUpForm.options.closed = $.proxy(function () {
                this.isFormPopUpVisible(false);
            }, this);

            this.popUpForm.options.modalCloseBtnHandler = $.proxy(this.onPopupCancel, this);
            this.popUpForm.options.keyEventHandlers = {
                escapeKey: $.proxy(this.onPopupCancel, this)
            };

            this.popUpForm.options.opened = function () {
                // Store temporary address for revert action in case when user click cancel action
                this.temporaryAddress = $.extend(true, {}, checkoutData.getShippingAddressFromData());
            };

            this.modalInstance = modal(this.popUpForm.options, $(this.popUpForm.element));
            return this.modalInstance;
        }
    });
});
