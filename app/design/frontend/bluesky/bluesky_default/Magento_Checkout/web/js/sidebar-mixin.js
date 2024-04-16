define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'underscore',
    'jquery-ui-modules/widget',
    'mage/decorate',
    'mage/collapsible',
    'mage/cookies',
    'jquery-ui-modules/effect-fade'
], function ($, authenticationPopup, customerData, alert, confirm, _) {
    'use strict';

    return function (widget) {

        $.widget('mage.sidebar', widget, {
            _create: function () {
                this._initContent();
                this._initUpdateMiniCart();
            },
            _initUpdateMiniCart: function () {
                var number_click = 1;
                var self = this;
                $('body').on('click', '.minicart-wrapper .qty-down-fixed-onclick', function(){
                    var input = $(this).closest('div.field').find('.cart-item-qty');
                    var val_input = input.val();
                    val_input = parseInt(val_input);
                    if(val_input <= number_click){
                        val_input = number_click;
                    }
                    else{
                        val_input = val_input - number_click;
                    }
                    input.val(val_input);
                    self._updateItemQty(input);
                    return false;
                });
                $('body').on('click', '.minicart-wrapper .qty-up-fixed-onclick', function(){
                    var input = $(this).closest('div.field').find('.cart-item-qty');
                    var val_input = input.val();
                    val_input = parseInt(val_input);
                    val_input = val_input + number_click;
                    input.val(val_input);
                    self._updateItemQty(input); 
                    return false;
                });

                $('.minicart-wrapper').on('change', '.cart-item-qty', function() {
                    self._updateItemQty($(this)); 
                });
            },
            _updateItemQty: function (elem) {
                var itemId = elem.data('cart-item');
                this._ajax(this.options.url.update, {
                    'item_id': itemId,
                    'item_qty': $('#cart-item-' + itemId + '-qty').val()
                }, elem, this._updateItemQtyAfter);
            },
            _updateItemQtyAfter: function (elem) {
                var productData = this._getProductById(Number(elem.data('cart-item')));
                if (!_.isUndefined(productData)) {
                    $(document).trigger('ajax:updateCartItemQty');
    
                    if (window.location.href === this.shoppingCartUrl) {
                        window.location.reload(false);
                    }
                }
                setTimeout(function() {
                    $('.minicart-wrapper').removeClass('start loading finish');
                }, 500);
            },
            _removeItemAfter: function (elem) {
                var productData = this._getProductById(Number(elem.data('cart-item')));
    
                if (!_.isUndefined(productData)) {
                    $(document).trigger('ajax:removeFromCart', {
                        productIds: [productData['product_id']],
                        productInfo: [
                            {
                                'id': productData['product_id']
                            }
                        ]
                    });
    
                    if (window.location.href.indexOf(this.shoppingCartUrl) === 0) {
                        window.location.reload();
                    }
                }
                setTimeout(function() {
                    $('.minicart-wrapper').removeClass('start loading finish');
                }, 500);
            },
            _ajax: function (url, data, elem, callback) {
                $.extend(data, {
                    'form_key': $.mage.cookies.get('form_key')
                });
    
                $.ajax({
                    url: url,
                    data: data,
                    type: 'post',
                    dataType: 'json',
                    context: this,
                    /** @inheritdoc */
                    beforeSend: function () {
                        elem.closest('.minicart-wrapper').addClass('start loading');
                    },
    
                    /** @inheritdoc */
                    complete: function () {
                        elem.closest('.minicart-wrapper').addClass('finish');
                    }
                }).done(function (response) {
                    var msg;
                    if (response.success) {
                        callback.call(this, elem, response);
                    } else {
                        msg = response['error_message'];

                        if (msg) {
                            alert({
                                content: msg
                            });
                        }
                        setTimeout(function() {
                            $('.minicart-wrapper').removeClass('start loading finish');
                        }, 500);
                    }
                })
                .fail(function (error) {
                    console.log(JSON.stringify(error));
                });
            },
        });

        return $.mage.SwatchRenderer;
    }
});