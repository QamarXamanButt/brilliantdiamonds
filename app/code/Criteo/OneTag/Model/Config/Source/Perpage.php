<?php

namespace Criteo\OneTag\Model\Config\Source;

class Perpage implements \Magento\Framework\Option\ArrayInterface {

    //dropdown values of number of product per feed page
    public function toOptionArray() {
        return [
            ['value' => 200, 'label' => '200'],
            ['value' => 300, 'label' => '300'],
            ['value' => 400, 'label' => '400'],
            ['value' => 500, 'label' => '500']
        ];
    }

}
