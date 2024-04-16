define([
    'jquery',
    'mage/translate',
    'mage/url',
    'Blueskytechco_QuickviewProduct/js/model/jquery.magnific-popup.min',
    'Blueskytechco_QuickviewProduct/js/blueskytechco_quickview',
    'slick'
], function ($, $t, url, magnificPopup, QuickView) {
    'use strict';

    function setCustomCookie(name,value,days)
	{
	  if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		  }
		  else var expires = "";
		  document.cookie = name+"="+value+expires+"; path=/";
	}

	function getCustomCookie(name)
	{
		  var nameEQ = name + "=";
		  var ca = document.cookie.split(';');
		  for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		  }
		  return null;
	}

	$(document).ready(function () {
        if ($('#switcher-currency').length || $('#switcher-language').length) {
            $('.setting-view').addClass('d-md-block');
        } else {
            $('.setting-view').removeClass('d-md-block');
        }

        if($('#newsletter-popup-common').length > 0 && !getCustomCookie('shownewsletter')){
        	var data_show = $('#newsletter-popup-common').data('show');
        	var get_width = $('#newsletter-popup-common').data('width');
        	$('#newsletter-popup-common').css({"max-width": get_width, "margin": "auto"});
        	var show_newsletter = false;
        	if(data_show == '1'){
        		if($('body').hasClass('cms-index-index')){
        			show_newsletter = true;
        		}
        	}
        	else{
        		show_newsletter = true;
        	}
        	if(show_newsletter){
                setTimeout(function(){
                    $.magnificPopup.open({
                        items: {
                            src: '#newsletter-popup-common',
                            type: 'inline'
                        },
                        removalDelay: 500,
                        closeOnBgClick: true,
                        preloader: true,
                        tLoading: '',
                        mainClass: 'mfp-zoom-in',
                        callbacks: {
                            open: function() {
                                $('.mfp-preloader').css('display', 'block');
                                $('body').addClass('loading-page-newsletter-magnific-popup');
                                var cal = $('#products-popup-common').find("div[data-appearance='carousel']").length;
                                if(cal > 0){
                                    var carouselElement = $('#products-popup-common').find('.elementor__content');
                                    carouselElement.slick('refresh');
                                    carouselElement.slick('setPosition');
                                }
                            },
                            close: function() {
                                $('body').removeClass('loading-page-newsletter-magnific-popup');
                                $('.mfp-preloader').css('display', 'none');
                            }
                        }
                    });
                }, 2000);
        	}
		}
		if($('#products-popup-common').length > 0){
			var data_show = $('#products-popup-common').data('show');
			var get_width = $('#products-popup-common').data('width');
			$('#products-popup-common').css({"max-width": get_width, "margin": "auto"});
			var show_products = false;
			if(data_show == '1'){
        		if($('body').hasClass('cms-index-index')){
        			show_products = true;
        		}
        	}
        	else{
        		show_products = true;
        	}
        	if(show_products){
        		var data_auto = parseInt($('#products-popup-common').data('timeout'));
        		setTimeout(function(){
					$.magnificPopup.open({
			        	items: {
						    src: '#products-popup-common',
						    type: 'inline'
						},
						removalDelay: 500,
	                    closeOnBgClick: true,
	                    preloader: true,
	                    tLoading: '',
	                    mainClass: 'mfp-zoom-in',
						callbacks: {
	                        open: function() {
	                        	$('.mfp-preloader').css('display', 'block');
	                            $('body').addClass('loading-page-newsletter-magnific-popup');
	                          	var cal = $('#products-popup-common').find("div[data-appearance='carousel']").length;
	                          	if(cal > 0){
	                          		var carouselElement = $('#products-popup-common').find('.elementor__content');
	                          		carouselElement.slick('refresh');
                					carouselElement.slick('setPosition');
	                          	}
	                        },
	                        close: function() {
	                          	$('body').removeClass('loading-page-newsletter-magnific-popup');
	                            $('.mfp-preloader').css('display', 'none');
	                        }
	                    }
			        });
				}, data_auto*1000);
        	}
		}
		if($('.product-image-container').length > 0){
			$(".product-image-container").each(function() {
				var get_c = $(this).data("hover");
				$(this).closest('.product-item-info').addClass(get_c);
			});
		}
		$('#new_check--show').click(function() {
		    if ($(this).is(':checked')) {
		    	$.magnificPopup.close();
		    	setCustomCookie("shownewsletter",'1',365)
		    }
		});
		$(document).on('click','.link-shortview', function() {
            var prodUrl = $(this).attr('data-shortview-url');
            if (prodUrl.length) {
                $.magnificPopup.open({
                    items: {
                      src: prodUrl
                    },
                    type: 'ajax',
                    removalDelay: 500,
                    closeOnBgClick: true,
                    preloader: true,
                    tLoading: '',
                    mainClass: 'mfp-zoom-in',
                    callbacks: {
                        open: function() {
                          	$('.mfp-preloader').css('display', 'block');
                          	$('.mfp-close').css('display', 'none');
                          	$('body').addClass('product-short-magnific-popup');
                        },
                        close: function() {
                        	$('body').removeClass('product-short-magnific-popup');
                          	$('.mfp-preloader').css('display', 'none');
                          	$('.mfp-close').css('display', 'block');
                        }
                    }
                });
            }
        });

        $(document).on('click', '.btn__top--header--banner--close', function(){
			$('.section-top-header').slideUp();
			return false;
		});

        $(document).on('click', '.promotion_discount a', function(){
            var code = $(this).closest('.promotion_discount').find('.discount-code').val();
			var $temp = $("<input>");
            $("#newsletter-popup-common").append($temp);
            $temp.val(code).select();
            document.execCommand("copy");
            $temp.remove();
            $(this).find('span').html($t('Copied'));
			return false;
		});
        
        $(document).on('click', '.grid-mode-show-type-products a', function(){
			$('.grid-mode-show-type-products a').removeClass('actived');
			$(this).addClass('actived');
			var data_view_mode = $('.container-products-switch').attr('data-view-mode');
			var view_mode = $(this).attr('data-grid-mode');
			$('.container-products-switch').removeClass('category_page_grid_'+data_view_mode);
			$('.container-products-switch').attr('data-view-mode',view_mode);
			$('.container-products-switch').addClass('category_page_grid_'+view_mode);
			return false;
		});

        $('body').on('click', '.cat_filter .btn_filter', function(){
            var screenWidth = $(window).width();
            if($('body').hasClass('filter-active')){
                $('body').removeClass('filter-active');
                $('#layered-filter-block').removeClass('active');
                if (screenWidth > 991 && !$('body').hasClass('catalog-category-sidebar-canvas')) {
                    $('.filter-options').slideUp(300);
                }
            }else{
                $('body').addClass('filter-active');
                $('#layered-filter-block').addClass('active');
                if (screenWidth > 991 && !$('body').hasClass('catalog-category-sidebar-canvas')) {
                    $('.filter-options').slideDown(300);
                }
            }
            return false;
        });

        $('body').on("click", function(event){
            var screenWidth = $(window).width();
            var $trigger = $("#narrow-by-list");
            if (screenWidth > 991 && ($('body').hasClass('catalog-category-grid') || $('body').hasClass('catalog-category-packery') || $('body').hasClass('catalog-category-masonry')) && $('body').hasClass('page-layout-1column')) {
                if($trigger !== event.target && !$trigger.has(event.target).length){
                    $('body').removeClass('filter-active');
                    $('#layered-filter-block').removeClass('active')
                    $('.filter-options').slideUp(500);
                }
            }
        });	

        $(document).on('click', '#layered-filter-block .filter-title', function(){
			if($('body').hasClass('filter-active')){
                $('body').removeClass('filter-active');
                $('#layered-filter-block').removeClass('active');
            }else{
                $('body').addClass('filter-active');
                $('#layered-filter-block').addClass('active');
            }
		});

		$(document).on('click', '.static-menu-click', function(){
			var current_this = $(this);
			if(current_this.hasClass('more-action')){
				current_this.removeClass('more-action');
				current_this.addClass('less-action');
				current_this.closest('div[data-content-type="staticmenu"]').find('.elementor-content-static-menu').slideDown("slow");
			}
			else{
				current_this.removeClass('less-action');
				current_this.addClass('more-action');
				current_this.closest('div[data-content-type="staticmenu"]').find('.elementor-content-static-menu').slideUp("slow");
			}
			return false;
		});

		if($("div").hasClass( "products-list" )){
			$(".grid-mode-show-type-products").hide();
		}
		
		$('#back-top').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
		
		if($('#purchase-fake-order').length > 0){
			var show_number_seconds = parseInt($('#purchase-fake-order').attr('data-seconds-displayed'));
			var show_number_hide = parseInt($('#purchase-fake-order').attr('data-seconds-hide'));
			var url_fake = $('#purchase-fake-order').attr('data-url');
			var interval_fake_order = setInterval(getProductRandom, show_number_seconds*1000);
			var stop_now = false;
			$(document).on('click', '#purchase-fake-order .purchase-close', function(){
				clearInterval(interval_fake_order);
				stop_now = true;
				$('#purchase-fake-order').removeClass('fadeInUp');
				$('#purchase-fake-order').addClass('fadeOutDown');
                return false;
			});
			function getProductRandom(){
				clearInterval(interval_fake_order);
                if(stop_now){
                    return false;
                }
				if(!$('#purchase-fake-order').hasClass('fadeInUp')){
					$.getJSON(url_fake, function( data ) {
						if(data.html != ""){
							$('#purchase-fake-order .product-purchase').html(data.html);
							$('#purchase-fake-order').removeClass('fadeOutDown');
							$('#purchase-fake-order').addClass('fadeInUp');
							$('#purchase-fake-order').removeAttr("style");
							setTimeout(function(){
								$('#purchase-fake-order').removeClass('fadeInUp');
								$('#purchase-fake-order').addClass('fadeOutDown'); 
								interval_fake_order = setInterval(getProductRandom, show_number_seconds*1000);
							}, show_number_hide*1000);
						}
					});
				}
				else{
					$('#purchase-fake-order').removeClass('fadeInUp');
					$('#purchase-fake-order').addClass('fadeOutDown'); 
				}
			}
		}

        var product_items = '.widget-product-advanced-grid.products-grid',
            btn_load = '.advanced-load-more',
            addToCartButtonSelector = '.action.tocart';
        $('body').on('click', btn_load, function(e){
            e.preventDefault();
            var button = $(this),
                data_json = JSON.parse(button.closest('.button-load-more').find('.productAdvancedInfo').text()),
                curpage = parseInt(button.attr('data-curpage')),
                totalpage = parseInt(button.attr('data-totalpage'));
            var page = curpage + 1;
            data_json.page = page;
            ajaxLoadProducts(product_items, button, data_json, function(data) {
                var products = button.closest('.widget-product-advanced-grid-default').find(product_items),
                    $itemContainer = $(product_items, data).eq(0);
                if ($itemContainer) {
                    var content_html = $itemContainer.html();
                    products.append(content_html).trigger('contentUpdated');
                    $(document).trigger('swatches-visible');
                    $(addToCartButtonSelector).attr('disabled', false);
                    if (totalpage == page) {
                        button.closest('.button-load-more').remove();
                    } else {
                        button.attr("data-curpage", page); 
                    }
                    var opstionQuickView = {
                        quickviewUrl: url.build('blueskytechco_quickview/product/view/'),
                        buttonText: data_json.qv_label,
                        actionInsert: data_json.qv_position,
                        classInsertPosition: data_json.qv_class_position,
                        classGallery: data_json.qv_add_class
                    }
                    QuickView(opstionQuickView);
                } 
            });
            return false;
        });

        function ajaxLoadProducts(product_items, btn, data_json, callback) { 
            var ajaxurl = url.build('productwidgetadvanced/product/loadmore');
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: data_json,
                beforeSend: function() {
                    btn.addClass('loading');
                    btn.prop('disabled', true);
                },
                success: function(data) {
                    btn.removeClass('loading');
                    btn.prop('disabled', false);
                    callback( data );
                },
                error: function(data) {
                    console.log('ajax error');
                },
            });
        }
        

		var scrolled_back = false;
		var offset = $('.page-header').outerHeight();
		if($('.section-top-header').length > 0){
			offset = offset + $('.section-top-header').outerHeight()+6;
		}
		var prevScrollpos = window.pageYOffset;
		if ($('.page-header .header-container').length > 0) {
			var headerSpaceH = $('.page-header .sticky-header').outerHeight(true);
			$('.page-header .sticky-header').after('<div class="headerSpace unvisible" style="height: ' + headerSpaceH + 'px;" ></div>');
		}
		$('.headerSpace').css('height', headerSpaceH);
		$(window).scroll(function () {
			var screenWidth = $(window).width();
			if($('#back-top').length > 0){
				if ($(this).scrollTop() > 400 && !scrolled_back) {
					$('#back-top').addClass('show');
					scrolled_back = true;
				}
				if ($(this).scrollTop() <= 400 && scrolled_back) {
					$('#back-top').removeClass('show');
					scrolled_back = false;
				}
			}
			var show_mobile = false;
			if($('body').hasClass('enable__sticky--header--mobile') || screenWidth >= 768){
				show_mobile = true;
			}

			var winScroll = $(this).scrollTop();
			var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
			var scrolled = (winScroll / height) * 100;
			$("#bls__back-top").css("height",  scrolled + "%");
			
			if($('body').hasClass('enable__sticky--header') && show_mobile){
				var currentScroll = $(window).scrollTop();
				var currentScrollPos = window.pageYOffset;
				var menu_slide = $('.slideshow-custom .menu-slide');
				if(currentScroll <= offset) {
		        	$(".header-container").removeClass("sticky");
		        	$(".header-container").removeClass('header_scroll_up');
					$(".headerSpace").addClass("unvisible");
					if (menu_slide.length) {
						$("body").removeClass("vertical-opend");
						$("#vertical-menu-container").find('.title-menu-dropdown').removeClass("active");
						$("#vertical-menu-container").find('.verticalmenu-list').hide();
					}
		        } 
		        else{
		        	$(".header-container").addClass("sticky");
		        	$(".header-container").addClass('header_scroll_up');
		        	$('.headerSpace').removeClass("unvisible");
		        }
		        prevScrollpos = currentScrollPos;
			}
		});
	});
});
