define([
    'jquery'
], function ($) {
    'use strict';

    return function (Component) {
        return Component.extend({

            getCartParam: function (name) {
                if (name == 'summary_count') {
                    var count = this.getCartParamUnsanitizedHtml(name);
                    if ($('.link-cart-bottom .count').length) {
                        $('.link-cart-bottom .count').html(count);
                    }
                    if ($('.link-cart-bottom .counter.qty').length) {
                        $('.link-cart-bottom .counter.qty').html(count);
                    }
                }
                return this.getCartParamUnsanitizedHtml(name);
            }
        });
    }
});