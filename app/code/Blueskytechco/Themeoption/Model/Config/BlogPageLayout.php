<?php
namespace Blueskytechco\Themeoption\Model\Config;

class BlogPageLayout implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'grid-3', 'label' => __('Grid 3 Columns')],
            ['value' => 'grid', 'label' => __('Grid 4 Columns')],
            ['value' => 'list', 'label' => __('List')],
            ['value' => 'grid-2', 'label' => __('Grid with Sidebar')],
        ];
    }

    public function toArray()
    {
        return [
            'grid-3' => __('Grid 3 Columns'),
            'grid' => __('Grid 4 Columns'),
            'grid-2' => __('Grid with Sidebar'),
            'list' => __('Blog List'),
        ];
    }
}