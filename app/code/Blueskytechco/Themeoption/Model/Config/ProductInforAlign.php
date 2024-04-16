<?php
namespace Blueskytechco\Themeoption\Model\Config;

class ProductInforAlign implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'default', 'label' => __('Default')],
            ['value' => 'product-align-center', 'label' => __('Center')],
        ];
    }

    public function toArray()
    {
        return [
            'default' => __('Default'),
            'product-align-center' => __('Center'),
        ];
    }
}