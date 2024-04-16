define([
    'jquery',
    'Magento_PageBuilder/js/events',
    'jquery-ui-modules/tabs'
], function ($, events) {
    'use strict';

    return function (config, element) {
        var $element = $(element);

        // Ignore stage builder preview tabs
        if ($element.is('.pagebuilder-tabs')) {
            return;
        }

        // Disambiguate between the mage/tabs component which is loaded randomly depending on requirejs order.
        $.ui.tabs({
            active: $element.data('activeTab') || 0,
            create:

                /**
                 * Adjust the margin bottom of the navigation to correctly display the active tab
                 */
                function () {
                    var borderWidth = parseInt($element.find('.tabs-content').css('borderWidth').toString(), 10);

                    $element.find('.tabs-navigation').css('marginBottom', -borderWidth);
                    $element.find('.tabs-navigation li:not(:first-child)').css('marginLeft', -borderWidth);
                },
            activate:

                /**
                 * Trigger redraw event since new content is being displayed
                 */
                function () {
                    events.trigger('contentType:redrawAfter', {
                        element: element
                    });
                }
        }, element);

        var dataShowDropdown = $element.data('show-dropdown');
        if(dataShowDropdown == true){
            var getActiveTitle = '';
            var $tabElements = $element.find("ul.tabs-navigation");
            var $containerSelectTabDropdown = $element.find(".container-select-tab__dropdown");
            $tabElements.hide();
            $tabElements.find("li" ).each(function() {
                if($(this).hasClass('ui-tabs-active')){
                    getActiveTitle = $(this).find('span.tab-title').text();
                    return false;
                }
            });
            if(getActiveTitle != ''){
                $containerSelectTabDropdown.find('.label-tab__dropdown').text(getActiveTitle);
            }

            $($element).on("click", 'ul.tabs-navigation a.tab-title', function(e) {
                getActiveTitle = $(this).find('span.tab-title').text();
                $containerSelectTabDropdown.find('.label-tab__dropdown').text(getActiveTitle);
                $tabElements.hide();
                $containerSelectTabDropdown.removeClass('select-arrow__active');
            });
            $($element).on("click", '.container-select-tab__dropdown', function(e) {
                if($(this).hasClass('select-arrow__active')){
                    $tabElements.hide();
                    $(this).removeClass('select-arrow__active');
                }
                else{
                    $tabElements.show();
                    $(this).addClass('select-arrow__active');
                }
                return false;
            });
        }
    };
});