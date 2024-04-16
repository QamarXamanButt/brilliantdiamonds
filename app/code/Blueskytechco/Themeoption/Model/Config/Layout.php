<?php
namespace Blueskytechco\Themeoption\Model\Config;

class Layout implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Default')],
            ['value' => '1200px', 'label' => __('1200px')],
            ['value' => '1170px', 'label' => __('1170px')],
            ['value' => '1370px', 'label' => __('1370px')],
            ['value' => '1440px', 'label' => __('1440px')],
            ['value' => '1630px', 'label' => __('1600px')],
            ['value' => '1920px', 'label' => __('1920px')],
            ['value' => '100%', 'label' => __('Full Width')],
            ['value' => 'box-layout', 'label' => __('Box Layout')]
        ];
    }

    public function toArray()
    {
        return [
            '' => __('Default'),
            '1200px' => __('1200px'),
            '1170px' => __('1170px'),
            '1280px' => __('1370px'),
            '1440px' => __('1440px'),
            '1630px' => __('1600px'),
            '1920px' => __('1920px'),
            '100%' => __('Full Width'),
            'box-layout' => __('Box Layout')
        ];
    }
}
