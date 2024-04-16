/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * @param {String} url
     * @param {*} fromPages
     */
    function processReviews(url, fromPages) {
        $.ajax({
            url: url,
            cache: true,
            dataType: 'html',
            showLoader: false,
            loaderContext: $('.product.data.items')
        }).done(function (data) {
            $('#product-review-container').html(data).trigger('contentUpdated');
            $('[data-role="product-review"] .pages a').each(function (index, element) {
                $(element).click(function (event) { //eslint-disable-line max-nested-callbacks
                    processReviews($(element).attr('href'), true);
                    event.preventDefault();
                });
            });
        }).always(function () {
            if (fromPages == true) { //eslint-disable-line eqeqeq
                $('html, body').animate({
                    scrollTop: $('#reviews').offset().top - 50
                }, 300);
            }
        });
    }

    return function (config) {

        processReviews(config.productReviewUrl);

        $(function () {
            $('.product-info-main .reviews-actions a').click(function (event) {
                var anchor, addReviewBlock, addReviewBlockTab, tabTabelReviewsTitle;

                event.preventDefault();
                anchor = $(this).attr('href').replace(/^.*?(#|$)/, '');
                addReviewBlock = $('#' + anchor);
                addReviewBlockTab = $('#tab-label-' + anchor);
                tabTabelReviewsTitle = $('#tab-label-'+ anchor +'-title');

                if (addReviewBlockTab.length) {
                    tabTabelReviewsTitle.trigger("click");
                    $('html, body').animate({
                        scrollTop: addReviewBlockTab.offset().top - 100
                    }, 300);
                }
            });
        });
    };
});
