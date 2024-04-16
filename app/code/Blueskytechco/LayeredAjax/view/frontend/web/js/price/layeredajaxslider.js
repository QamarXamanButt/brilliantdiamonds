define([
    'jquery',
    'Blueskytechco_LayeredAjax/js/price/ion.rangeSlider.min',
    'Blueskytechco_QuickviewProduct/js/blueskytechco_quickview',
    'jquery/ui',
    'Blueskytechco_LayeredAjax/js/layeredajax'
], function($, ionRangeSlider, QuickView) {
    "use strict";

    $.widget('blueskytechco.layeredAjaxSlider', $.blueskytechco.layeredAjax, {
        options: {
            sliderElement: '#price-range-slider'
        },
        _create: function () {
            var self = this;
            $(this.options.sliderElement).ionRangeSlider({
				type: "double",
                min: self.options.selectedFromMin,
                max: self.options.selectedToMax, 
                from: self.options.selectedFrom,
                to: self.options.selectedTo,
                prettify_enabled: true,
                prefix: self.options.currency,
                grid: true,
                onFinish: function(obj) {
					self.ajaxSubmit(self.getUrl(obj.from, obj.to));

                    var opstionQuickView = {
                        quickviewUrl: self.options.quickviewUrl,
                        buttonText: self.options.labelQuickview,
                        actionInsert: self.options.positionQuickview,
                        classInsertPosition: self.options.classPositionQuickview,
                        classGallery: self.options.addClassQuickview
                    }
                    QuickView(opstionQuickView);
                }
            });
        }, 

        getUrl: function(from, to){
            return this.options.ajaxUrl.replace(encodeURI('{price_start}'), from).replace(encodeURI('{price_end}'), to);
        },
    });

    return $.blueskytechco.layeredAjaxSlider;
});
