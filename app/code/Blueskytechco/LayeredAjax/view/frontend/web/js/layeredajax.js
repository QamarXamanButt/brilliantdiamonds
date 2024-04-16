/**
 * Copyright ? 2022 Blueskytechco. All rights reserved.
 * See https://blueskytechco.com/ for license details.
 */
 define([
    'jquery',
    'mage/translate',
    'Blueskytechco_QuickviewProduct/js/blueskytechco_quickview',
    'jquery/ui',
    'productListToolbarForm'
], function ($, $t, QuickView) {
    "use strict";

    $.widget('blueskytechco.layeredAjax', {

        options: {
            productsListSelector: '#layered-ajax-list-products',
            navigationSelector: '#layered-filter-block'
        },

        _create: function () {
            this.initProductListUrl();
            this.initObserve();
        },

        initProductListUrl: function () {
            var self = this;
            $.mage.productListToolbarForm.prototype.changeUrl = function (paramName, paramValue, defaultValue) {
                var urlPaths = this.options.url.split('?'),
                    baseUrl = urlPaths[0],
                    urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                    paramData = {},
                    parameters;
                for (var i = 0; i < urlParams.length; i++) {
                    parameters = urlParams[i].split('=');
                    paramData[parameters[0]] = parameters[1] !== undefined
                        ? window.decodeURIComponent(parameters[1].replace(/\+/g, '%20'))
                        : '';
                }
                paramData[paramName] = paramValue;
                if (paramValue == defaultValue) {
                    delete paramData[paramName];
                }
                paramData = $.param(paramData);

                self.ajaxSubmit(baseUrl + (paramData.length ? '?' + paramData : ''));
            }
        },

        initObserve: function () {
            var self = this;
            var aElements = this.element.find('a');
            aElements.each(function (index) {
                var el = $(this);
                var link = self.checkUrl(el.prop('href'));
                if(!link) return;

                el.on('click', function (e) {
                    if (el.hasClass('swatch-option-link-layered')) {
                        var childEl = el.find('.swatch-option');
                        childEl.addClass('selected');
                    } else {
                        var checkboxEl = el.find('input[type=checkbox]');
                        checkboxEl.prop('checked', !checkboxEl.prop('checked'));
                    }

                    self.ajaxSubmit(link);
                    e.stopPropagation();
                    e.preventDefault();
                });

                var checkbox = el.find('input[type=checkbox]');
                checkbox.on('click', function (e) {
                    self.ajaxSubmit(link);
                    e.stopPropagation();
                });
            });

            if ($( window ).width() < 992) {
                if (self.options.opendTabMobile == 1) {
                    $(".filter-options-item").addClass('active');
                    $(".filter-options-item .filter-options-content").show();
                } else {
                    $(".filter-options-item").removeClass('active');
                    $(".filter-options-item .filter-options-content").hide();
                }
            }

            $(".filter-options-title").on('click', function (e) {
                if ($(this).closest('.filter-options-item').hasClass('active')) {
                    $(this).closest(".filter-options-item").removeClass('active');
                    $(this).closest(".filter-options-item").find(".filter-options-content").slideUp(500);
                } else {
                    $(this).closest(".filter-options-item").addClass('active');
                    $(this).closest(".filter-options-item").find(".filter-options-content").slideDown(500);
                }
            });

            $("#layered-filter-block .expand-item-link").on('click', function (e) {
                if($(this).hasClass("expanding")){
                    $(this).find('a').text($t('Show More'));
                }else{
                    $(this).find('a').text($t('Show Less'));
                }
                $(this).toggleClass('expanding');
                $(this).closest('ol').find('.orther-link').slideToggle('slow');
            });
            
            $(".filter-current a").on('click', function (e) {
                var link = self.checkUrl($(this).prop('href'));
                if(!link) return;

                self.ajaxSubmit(link);
                e.stopPropagation();
                e.preventDefault();
            });

            $(".filter-actions a").on('click', function (e) {
                var link = self.checkUrl($(this).prop('href'));
                if(!link) return;
                self.ajaxSubmit(link);
                e.stopPropagation();
                e.preventDefault();
            });
        },

        clickOnScrollButton: function ($btn, $holder) {
            if($btn.length == 0){
                $btn = $('.products-footer .category-load-more');
            }
            if ( $btn.length == 0 || $holder.length == 0 || !$btn.hasClass('load-on-scroll') ) return;
            new Waypoint({
                element: $holder[0],
                handler: function(direction) {
                    this.destroy();
                    $btn.trigger('click');
                },
                offset: "bottom-in-view"
            });
        },

        checkUrl: function (url) {
            var regex = /(http|https):\/\/(\w+:{0,1}\w*)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/;

            return regex.test(url) ? url : null;
        },

        ajaxSubmit: function (submitUrl) {
            var self = this;

            $.ajax({
                url: submitUrl,
                data: {isAjax: 1},
                type: 'post',
                dataType: 'json',
                beforeSend: function () {
                    $('body').addClass('ajax-filter');
                    $('.layered_ajax_overlay').show();
                    if (typeof window.history.pushState === 'function') {
                        window.history.pushState({url: submitUrl}, '', submitUrl);
                    }
                },
                success: function (res) {
                    if (res.backUrl) {
                        window.location = res.backUrl;
                        return;
                    }
                    if (res.navigation) {
                        $(self.options.navigationSelector).replaceWith(res.navigation);
                        $(self.options.navigationSelector).trigger('contentUpdated');
                    }
                    if (res.products) {
                        $(self.options.productsListSelector).replaceWith(res.products);
                        $(self.options.productsListSelector).trigger('contentUpdated');
                    }
                    if (res.product_list_mode == 'list') {
                        $(".grid-mode-show-type-products").hide();
                    } else {
                        $(".grid-mode-show-type-products").show();
                    }
                    if (res.page_load_more) {
                        var next = $('.toolbar .pages .next'),
                            product_items = '.products.list.items.product-items',
                            add_class = '',
                            products = $(product_items),
                            $btn = $('.products-footer .category-load-more.ajax-true');
                        $('.toolbar .pages').hide();
                        var count = $(product_items).children().length,
                            toolbar_amount;
                        if (count > 1) {
                            toolbar_amount = '<span>'+count+' Items</span>';
                        } else {
                            toolbar_amount = '<span>'+count+' Item</span>';
                        }
                        $('.toolbar-amount').html(toolbar_amount);
                        if (next.length) {
                            var next_url = next.attr('href');
                            if(typeof(next_url) != 'undefined') {
                                next_url += '&load_more=1';
                            } else {
                                next_url = '';
                            }
                            if (next_url) {
                                if (res.page_load_more == 'scroll') {
                                    add_class = ' load-on-scroll';
                                }
                                var next_html = '<div class="products-footer">'+
                                    '<a href="'+next_url+'" class="category-load-more ajax-true btn action primary'+add_class+'"><span>'+$t('Load More')+'</span></span></a>'+
                                '</div>';
                                products.after(next_html);
                            }
                        }
                        self.clickOnScrollButton($btn, products);
                    }
                    $('body').removeClass('ajax-filter');
                    $('body').addClass('load-end');
                    setTimeout(function() {
                        $('body').removeClass('load-end');
                        if($('.product-image-container').length > 0){
                          $(".product-image-container").each(function() {
                            var get_c = $(this).data("hover");
                            $(this).closest('.product-item-info').addClass(get_c);
                          });
                        }
                    }, 300);
                    $('.layered_ajax_overlay').hide();

                    if (self.options.quickviewUrl) {
                        var opstionQuickView = {
                            quickviewUrl: self.options.quickviewUrl,
                            buttonText: self.options.labelQuickview,
                            actionInsert: self.options.positionQuickview,
                            classInsertPosition: self.options.classPositionQuickview,
                            classGallery: self.options.addClassQuickview
                        }
    
                        QuickView(opstionQuickView);
                    }
                },
                error: function () {
                    window.location.reload();
                }
            });
        }
    });

    return $.blueskytechco.layeredAjax;
});
