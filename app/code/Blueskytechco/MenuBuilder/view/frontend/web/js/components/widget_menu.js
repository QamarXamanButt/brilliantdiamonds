define([
    "jquery",
	'mage/translate'
], function ($, $t) { 
    'use strict';

    $.widget('mage.widgetMenu', {
        options: {
        },
        _create: function () {

			$('body').on('click', '.widget-menu-list li.ui-menu-item .open-children-toggle', function(){
				if(!$(this).parent().hasClass("opened-is")) {
					$(this).parent().addClass("opened-is");
				} else {
					$(this).parent().removeClass("opened-is");
				}
			});
             
            $('body').on('click', '.widget-menu-list li.ui-menu-item .back-main-menu', function(){
				$(this).closest('li.ui-menu-item').removeClass("opened-is");
			});

			$('body').on('click', '.widget-menu-list .close-main-menu', function(){
				if($("html").hasClass("nav-open")) {
					$("html").removeClass("nav-open");
					$("html").removeClass("nav-before-open");
                    $("li.ui-menu-item").removeClass("opened-is");
				}
				return false;
			});
        },
    });
    return $.mage.widgetMenu;
});