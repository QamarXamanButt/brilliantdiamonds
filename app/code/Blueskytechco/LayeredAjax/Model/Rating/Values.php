<?php
namespace Blueskytechco\LayeredAjax\Model\Rating;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;

class Values extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    protected $optionFactory;

    public function getAllOptions()
    {
        $this->_options =[ 
            ['label'=>'Select Options', 'value'=>''],
            ['label'=>'5 Rating', 'value'=>'5'],
            ['label'=>'4 Rating', 'value'=>'4'],
            ['label'=>'3 Rating', 'value'=>'3'],
            ['label'=>'2 Rating', 'value'=>'2'],
            ['label'=>'1 Rating', 'value'=>'1']
        ];
        return $this->_options;
    }

}