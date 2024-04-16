<?php
namespace Blueskytechco\Themeoption\Model\Config;

class TypeLoadImage implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'append', 'label' => __('Append Image')], 
            ['value' => 'replace', 'label' => __('Replace Image')]
        ];
    }

    public function toArray()
    {
        return [
            'append' => __('Append Image'), 
            'replace' => __('Replace Image')
        ];
    }
}
