<?php
namespace Blueskytechco\Themeoption\Model\Config;

class ProductStyle implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'product__style-1', 'label' => __('Style 1 - Default')],
            ['value' => 'product__style-2', 'label' => __('Style 2')],
            ['value' => 'product__style-3', 'label' => __('Style 3')],
            ['value' => 'product__style-4', 'label' => __('Style 4')],
        ];
    }

    public function toArray()
    {
        return [
            'product__style-1' => __('Style 1 - Default'),
            'product__style-2' => __('Style 2'),
            'product__style-3' => __('Style 3'),
            'product__style-4' => __('Style 4'),
        ];
    }
}