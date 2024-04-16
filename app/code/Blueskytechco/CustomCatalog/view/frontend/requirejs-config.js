var config = {
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'Blueskytechco_CustomCatalog/js/product/swatch-renderer-mixin': true
            },
            'Magento_Checkout/js/view/minicart': {
                'Blueskytechco_CustomCatalog/js/view/minicart': true
            },
            'Magento_ConfigurableProduct/js/configurable': {
                'Blueskytechco_CustomCatalog/js/product/configurable-mixin': true
            }
        }
    }
};