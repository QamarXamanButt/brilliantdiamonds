define([
    'jquery',
    'mage/translate',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'Blueskytechco_AjaxSuite/js/model/ajaxsuite-popup',
    'mage/validation/validation'
], function ($, $t, url, customerData, ajaxsuitepopup) {
    'use strict';

    $.widget('blueskytechco.ajaxsuite', {
        options: {
                popupWrapperSelector : '#mb-ajaxsuite-popup-wrapper',
                ajaxCart: {
                    enabled: 0,
                    continueShoppingSelector: '#button_continue_shopping',
                    minicartSelector: '[data-block="minicart"]',
                    messagesSelector: '[data-placeholder="messages"]',
                    initConfig: {
                        'show_success_message': true,
                        'timerErrorMessage': 3000,
                        'addWishlistItemUrl': null
                    },
                    formKey: null,
                    formKeyInputSelector: 'input[name="form_key"]',
                    addToCartButtonSelector: 'button.tocart',
                    addToCartTierButtonSelector: 'button.tier-addcart',
                    addToCartUrl: null,
                    addToCartInWishlistUrl: null,
                    wishlistAddToCartUrl: null,
                    checkoutCartUrl: null,
                    addToCartButtonDisabledClass: 'loading',
                    addToCartButtonTextWhileAdding: $t('Adding...'),
                    addToCartButtonTextAdded: $t('Added'),
                    addToCartButtonTextDefault: $t('Add to Cart')
                },
                ajaxWishList: {
                    enabled: 0,
                    WishlistUrl: null,
                    wishlistBtnSelector: '[data-action="add-to-wishlist"]',
                    btnCloseSelector: '#ajaxwishlist_btn_close_popup',
                    btnCancelSelector: '#ajaxwishlist_btn_cancel',
                    btnToLoginSelector: '#ajaxwishlist_btn_to_login'
                },
                ajaxCompare: {
                    enabled: 0,
                    compareSelector: '.tocompare',
                    CompareUrl: null,
                },
                quickView: {
                    enabled: 0
                },
                popupSelector: '#ajaxsuite-popup-content',
                cartBottom: '.link-cart-bottom',
                loginUrl: null,
                customerId: null

        },
        _create: function() {
            this._bind();
            this.options.popupWrapper = $('<div />', {
                    'id': 'mb-ajaxsuite-popup-wrapper'
                }).appendTo($('#ajaxsuite-popup-content'));
        },
        showModal: function (element) {
            ajaxsuitepopup.createPopUp(element);
            ajaxsuitepopup.showModal();
            return ajaxsuitepopup;
        },
        getCustomerData: function()
        {
            var customer = customerData.get('customer');
            if (!customer().firstname) {
                return false;
            } 
            return true;
        },
        initEvents: function()
        {
            if (!$('.scd__countdown_cart_mini .countdown-time').length) {
                var interval_fake_time = setInterval(function() {
                    var data_timeout_message = $('.scd__countdown_cart_mini').attr('data_timeout_message'),
                        data_timeout_time = $('.scd__countdown_cart_mini').attr('data_timeout_time'),
                        html_fake_time = '';
                    if (data_timeout_message && data_timeout_time) {
                        html_fake_time += '<div class="countdown-time"><span>&#x1F525; '+data_timeout_message+'</span> ';
                        html_fake_time += '<strong class="font-medium timer">';
                            html_fake_time += '<span class="countdown-timer-minute">00 m</span>';
                            html_fake_time += '<span class="countdown-timer-sec -ml-1">00 s</span>';
                            html_fake_time += '</strong></div>';
                        $('.scd__countdown_cart_mini').html(html_fake_time);
                        var timer_timeout = data_timeout_time+":00";
                        var interval = setInterval(function() {
                            var timer = timer_timeout.split(':');
                            var minutes = parseInt(timer[0], 10);
                            var seconds = parseInt(timer[1], 10);
                            --seconds;
                            minutes = (seconds < 0) ? --minutes : minutes;
                            if (minutes < 0) clearInterval(interval);
                            seconds = (seconds < 0) ? 59 : seconds;
                            seconds = (seconds < 10) ? '0' + seconds : seconds;
                            $('.scd__countdown_cart_mini .countdown-timer-minute').html(minutes + 'm');
                            $('.scd__countdown_cart_mini .countdown-timer-sec').html(seconds + 's');
                            timer_timeout = minutes + ':' + seconds;
                        }, 1000);
                        if ($('.scd__countdown_cart_mini .countdown-time').length) {
                            clearInterval(interval_fake_time);
                        }
                    }
                }, 1000);
            } 
        },
        initEventsWishlist: function()
        {
            var self = this;
            var get_customer_data = this.getCustomerData();
            
            $('body').on('click','.account-link-header .link-account', function (e) {
                get_customer_data = self.getCustomerData();
                if (!get_customer_data && !$('.account-link-header .link-account').hasClass('account-login')) {
                    $(this).addClass("trigger-auth-popup").attr('data-action', 'ajax-popup-login').attr('href', 'javascript:void(0);').removeAttr("data-post");
                    e.preventDefault();
                    return;
                }
            });

            $('body').on('click',self.options.ajaxWishList.wishlistBtnSelector, function (e) {
                get_customer_data = self.getCustomerData();
                if (!get_customer_data && !$('.account-link-header .link-account').hasClass('account-login')) {
                    $(self.options.ajaxWishList.wishlistBtnSelector).addClass("trigger-auth-popup").attr('data-action', 'ajax-popup-login').attr('href', 'javascript:void(0);').removeAttr("data-post");
                    e.preventDefault();
                    return;
                }
                var _this_fixed = $(this);
                _this_fixed.addClass('loading');
                e.preventDefault();
                e.stopPropagation();
                if($(this).data('post'))
                {
                    var params = $(this).data('post').data;
                }else
                {
                    var params = {};
                }
                params['ajax_post'] = true;
                $('body').trigger('processStart');
                $.ajax({
                    url: self.options.ajaxWishList.WishlistUrl,
                    data: params,
                    type: 'post',
                    showLoader: false,
                    dataType: 'json',
                    success: function (res) {
                        ajaxsuitepopup.hideModal();
                        if (res.html_popup) {
                            self.options.popupWrapper.html(res.html_popup);
                            self.showModal(self.options.popupWrapper);
                        }
                        self.reloadCustomerData(['wishlist']);
                        _this_fixed.removeClass('loading');
                    },
                    error: function (res) {
                        alert('Error in sending ajax request');
                        _this_fixed.removeClass('loading');
                    }
                });
                $('body').trigger('processStop');
            });
        },
        initEventsCompare: function () {
            var self = this;
            $('body').on('click',self.options.ajaxCompare.compareSelector, function (e) {

                e.preventDefault();
                e.stopPropagation();
                var _this_fixed = $(this);
                _this_fixed.addClass('loading');
                var params = $(this).data('post').data;
                if($(this).data('post'))
                {
                    var params = $(this).data('post').data;
                }else
                {
                    var params = {};
                }
                $('body').trigger('processStart');
                $.ajax({
                    url: self.options.ajaxCompare.CompareUrl,
                    data: params,
                    type: 'post',
                    showLoader: false,
                    dataType: 'json',
                    success: function (res) {
                        ajaxsuitepopup.hideModal();
                        if (res.html_popup) {
                            self.options.popupWrapper.html(res.html_popup);
                            self.showModal(self.options.popupWrapper);
                        }
                        self.reloadCustomerData(['compare-products']);
                        _this_fixed.removeClass('loading');
                    },
                    error: function (res) {
                        alert('Error in sending ajax request');
                        _this_fixed.removeClass('loading');
                    }
                });
                $('body').trigger('processStop');
            });
        },
        initEventsAjaxCart: function()
        {
            var self = this;
            $('body').delegate(self.options.ajaxCart.addToCartButtonSelector, 'click', function (e) {
                var form = $(this).closest('form');
                if(form.length)
                {
                    var action = form.attr('action');
                    if(action.indexOf('checkout/cart/add') != -1)
                    {
                        e.preventDefault();
                        if ($(this).closest('.product-info-main').length) {
                            var dataForm = $(this).closest('form#product_addtocart_form');
                            var validate = dataForm.validation('isValid');
                            if (validate) {
                                var form = $(this).closest('form');
                                self.ajaxCartSubmit(form);
                            }
                            return;
                        }
                        else if($(this).closest('.product-item-info').length) {
                            var have_options = $(this).closest('.product-item-info').find('.swatch-attribute').length;
                            if(have_options){
                                var selected_options = $(this).closest('.product-item-info').find('.swatch-option.selected').length;
                                if(selected_options == have_options){
                                    $(this).closest('.product-item-info').removeClass('error_validation--product-options');
                                    $(this).closest('.product-item-info').find('.msg_validation--product-options').remove();
                                    self.ajaxCartSubmit(form);
                                }
                                else{
                                    if ($(this).closest('.product-item-info').find('.action.link-quickview').length) {
                                        $(this).closest('.product-item-info').find('.action.link-quickview').trigger('click');
                                    } else {
                                        self.ajaxCartSubmit(form);
                                    }
                                }
                                return;
                            }
                            else{
                                self.ajaxCartSubmit(form);
                            }
                            return;
                        }
                        self.ajaxCartSubmit(form);
                    }
                }
            });
            $('body').on('click', self.options.ajaxCart.continueShoppingSelector, function (e) {
                ajaxsuitepopup.hideModal();
            });

            $('body').on('click', self.options.ajaxCart.continueShoppingSelector, function (e) {
                ajaxsuitepopup.hideModal();
            });

            $(document).on('ajaxComplete', function (event, xhr, settings) {
                var parentBody = window.parent.document.body;
                var cart = customerData.get('cart')();
                if (settings.type.match(/get/i) && _.isObject(xhr.responseJSON)) {
                    var result = xhr.responseJSON;
                    var cartMessage = false;
                    var shippingFreeCanvas = false;
                    if (_.isObject(result.messages)) {
                        var messageLength = result.messages.messages.length;
                        var message = result.messages.messages[0];
                        if (messageLength && message.type == 'success') {
                            cartMessage = message.text;
                        }
                    }
                    if (_.isObject(result.cart) && _.isObject(result.messages)) {
                        var messageLength = result.messages.messages.length;
                        var message = result.messages.messages[0];
                        if (messageLength && message.type == 'success') {
                            cartMessage = message.text;
                            if (result.cart.shipping_free_canvas) {
                                shippingFreeCanvas = true;
                            }
                        }
                    }
                    if (cartMessage) {
                        window.parent.showMiniCart = true;
                        window.parent.showMiniCartPopup = false;
                        if (shippingFreeCanvas) {
                            window.parent.shippingFreeCanvas = true;
                        }
                        $('.mfp-close', parentBody).trigger('click');
                        return
                    }
                }
                if (settings.type.match(/get/i)
                    && settings.url.match(/customer\/section\/load/i)
                    && _.isObject(xhr.responseJSON) &&
                    xhr.responseJSON.cart
                ) {
                    if ($('body').hasClass('quickview_addcart')) {
                        $(self.options.ajaxCart.minicartSelector + ' a.showcart').trigger('click');
                        $('body').removeClass('quickview_addcart');
                    }
                    if($('body').hasClass('ajax_ld'))
                    {
                        $('body').removeClass('ajax_ld');
                        $('body').addClass('ajax_end');
                        $(self.options.ajaxCart.minicartSelector + ' a.showcart').trigger('click');
                        if (!$('.cart_thres_js').length) {
                            return;
                        }
                        if (!$('body').hasClass('shipping_free_canvas')){
                            if (cart.shipping_free_canvas) {
                                $('body').addClass('shipping_free_canvas');
                            }
                        }
                    }

                    if($(self.options.ajaxCart.minicartSelector).find('.showcart').hasClass('active'))
                    {
                        if (!$('.cart_thres_js').length) {
                            return;
                        }
                        if (!$('.cart_thres_js').hasClass('shipping_free')) {
                            $('.cart_thres_js').addClass('shipping_free');
                            if (cart.shipping_free_canvas) {
                                $('body').addClass('shipping_free_canvas');
                            }
                            return;
                        }
                        if (!$('body').hasClass('shipping_free_canvas')){
                            if (cart.shipping_free_canvas) {
                                $('body').addClass('shipping_free_canvas');
                            } else {
                                $('body').removeClass('shipping_free_canvas');
                            }
                        } else {
                            if (cart.shipping_free_canvas == false) {
                                $('body').removeClass('shipping_free_canvas');
                            }
                        }
                    }

                    if($('body').hasClass('checkout-cart-index'))
                    {
                        if (!$('.free-ship-calculated .cart_thres_js').length) {
                            return;
                        }
                        if (cart.shipping_free) {
                            $('.free-ship-calculated .cart_thres_js').html(cart.shipping_free);
                        } else {
                            return;
                        }
                        if ($('body').hasClass('checkout_cart_canvas')){
                            if (!$('body').hasClass('shipping_free_canvas')){
                                if (cart.shipping_free_canvas) {
                                    $('body').addClass('shipping_free_canvas');
                                } else {
                                    $('body').removeClass('shipping_free_canvas');
                                }
                            } else {
                                if (cart.shipping_free_canvas == false) {
                                    $('body').removeClass('shipping_free_canvas');
                                }
                            }
                        } else {
                            $('body').addClass('checkout_cart_canvas');
                            if (cart.shipping_free_canvas) {
                                $('body').addClass('shipping_free_canvas');
                            }
                        }
                    }

                    if ($('.scd__countdown_cart_mini').length && !$('.scd__countdown_cart_mini .countdown-time').length) {
                        var data_timeout_message = $('.scd__countdown_cart_mini').attr('data_timeout_message'),
                            data_timeout_time = $('.scd__countdown_cart_mini').attr('data_timeout_time'),
                            html_fake_time = '';
                        html_fake_time += '<div class="countdown-time"><span>&#x1F525; '+data_timeout_message+'</span> ';
                            html_fake_time += '<strong class="font-medium timer">';
                                html_fake_time += '<span class="countdown-timer-minute">00 m</span>';
                                html_fake_time += '<span class="countdown-timer-sec -ml-1">00 s</span>';
                                html_fake_time += '</strong></div>';
                        $('.scd__countdown_cart_mini').html(html_fake_time);
                        var timer_timeout = data_timeout_time+":00";
                        var interval = setInterval(function() {
                            var timer = timer_timeout.split(':');
                            var minutes = parseInt(timer[0], 10);
                            var seconds = parseInt(timer[1], 10);
                            --seconds;
                            minutes = (seconds < 0) ? --minutes : minutes;
                            if (minutes < 0) clearInterval(interval);
                            seconds = (seconds < 0) ? 59 : seconds;
                            seconds = (seconds < 10) ? '0' + seconds : seconds;
                            $('.scd__countdown_cart_mini .countdown-timer-minute').html(minutes + 'm');
                            $('.scd__countdown_cart_mini .countdown-timer-sec').html(seconds + 's');
                            timer_timeout = minutes + ':' + seconds;
                        }, 1000);
                    }
                }
            });

            $('body').delegate(self.options.ajaxCart.addToCartTierButtonSelector, 'click', function (e) {
                var form = $('.product-info-main').find('form');
                var quantity = $(this).data('quantity');
                var add = $(this);
                if(form.length)
                {
                    var action = form.attr('action');
                    if(action.indexOf('checkout/cart/add') != -1)
                    {
                        e.preventDefault();
                        if ($('.product-info-main').length) {
                            var dataForm = $('.product-info-main').find('form#product_addtocart_form');
                            var validate = dataForm.validation('isValid');
                            if (validate) {
                                self.ajaxTierCartSubmit(form, quantity, add);
                            }
                            return;
                        }
                    }
                }
            });
        },
        initUpdateCartButtom: function()
        {
            var self = this;
            $('body').on('click', self.options.cartBottom, function (e) {
                $(self.options.ajaxCart.minicartSelector + ' a.showcart').trigger('click');
                return false;
            });

            $("[data-block='minicart']").on("dropdowndialogopen", (e) => {
                setTimeout(function(){
                    $('body').removeClass('ajax_end');
                }, 500);
                $('html').addClass('hside_opened');          
            });
            $("[data-block='minicart']").on("dropdowndialogclose", (e) => {
                $('html').removeClass('hside_opened');
            });
        },
        ajaxTierCartSubmit: function (form, quantity, add) {
            var self = this;
            $(self.options.ajaxCart.minicartSelector).trigger('contentLoading');
            $('body').addClass('ajax_ld');
            var formData = new FormData(form[0]);
            formData.append("qty", quantity);
            $.ajax({
                url: form.attr('action').replace('checkout/cart', 'ajaxsuite/cart'),
                data: formData,
                type: 'post',
                dataType: 'json',
                showLoader: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (res) {
                    ajaxsuitepopup.hideModal();
                    if (res.success) {
                        if ($('.cart_thres_3').length) {
                            $('body').addClass('shipping_free_canvas');
                        } else {
                            $('body').removeClass('shipping_free_canvas');
                        }
                        self.reloadCustomerData(['cart']);
                    }
                    else if (res.error && res.url) {
                        window.location.href = res.url;
                    }else if (res.error && res.content) {
                        if(!form.closest(self.options.popupWrapperSelector).length)
                        {
                            self.options.popupWrapper.html(res.content);
                            self.showModal(self.options.popupWrapper);
                        }
                    }else if(res.error)
                    {
                        self.options.popupWrapper.html(res.error);
                        self.showModal(self.options.popupWrapper);
                        window.location.reload();
                    }
                    $(self.options.ajaxCart.minicartSelector).trigger('contentUpdated');
                }
            });
        },
        ajaxCartSubmit: function (form) {
            var self = this,formData;
            $(self.options.ajaxCart.minicartSelector).trigger('contentLoading');
            $('body').addClass('ajax_ld');
            self.disableAddToCartButton(form);
            formData = new FormData(form[0]);
            $.ajax({
                url: form.attr('action').replace('checkout/cart', 'ajaxsuite/cart'),
                data: formData,
                type: 'post',
                dataType: 'json',
                showLoader: false,
                cache: false,
                contentType: false,
                processData: false,
                success: function (res) {
                    ajaxsuitepopup.hideModal();
                    if (res.success) {
                        if ($('.cart_thres_3').length) {
                            $('body').addClass('shipping_free_canvas');
                        } else {
                            $('body').removeClass('shipping_free_canvas');
                        }
                        self.reloadCustomerData(['cart']);
                    }
                    else if (res.error && res.url) {
                        window.location.href = res.url;
                    }else if (res.error && res.content) {
                        if(!form.closest(self.options.popupWrapperSelector).length)
                        {
                            self.options.popupWrapper.html(res.content);
                            self.showModal(self.options.popupWrapper);
                        }
                    }else if(res.error)
                    {
                        self.options.popupWrapper.html(res.error);
                        self.showModal(self.options.popupWrapper);
                        window.location.reload();
                    }
                    self.enableAddToCartButton(form);
                    $(self.options.ajaxCart.minicartSelector).trigger('contentUpdated');
                }
            });
        },
        disableAddToCartButton: function (form) {
            var addToCartButton = $(form).find(this.options.ajaxCart.addToCartButtonSelector);
            addToCartButton.addClass(this.options.ajaxCart.addToCartButtonDisabledClass);
            addToCartButton.attr('title', this.options.ajaxCart.addToCartButtonTextWhileAdding);
            addToCartButton.find('span').text(this.options.ajaxCart.addToCartButtonTextWhileAdding);
        },
        enableAddToCartButton: function (form) {
            var self = this, addToCartButton = $(form).find(this.options.ajaxCart.addToCartButtonSelector);
            addToCartButton.find('span').text(this.options.ajaxCart.addToCartButtonTextAdded);
            addToCartButton.attr('title', this.options.ajaxCart.addToCartButtonTextAdded);

            setTimeout(function () {
                addToCartButton.removeClass(self.options.ajaxCart.addToCartButtonDisabledClass);
                addToCartButton.find('span').text(self.options.ajaxCart.addToCartButtonTextDefault);
                addToCartButton.attr('title', self.options.ajaxCart.addToCartButtonTextDefault);
            }, 1000);
        },
        reloadCustomerData: function(sessionName)
        {
            customerData.reload(sessionName, false);
        },
        _bind: function () {
            if(this.options.ajaxCart.enabled)
            {
               this.initEventsAjaxCart();
            }
            if(this.options.ajaxWishList.enabled)
            {
                this.initEventsWishlist();
            }
            if(this.options.ajaxCompare.enabled)
            {
                this.initEventsCompare();
            }
            this.initUpdateCartButtom();
            this.initEvents();
        }
    });
    return $.blueskytechco.ajaxsuite;
});
