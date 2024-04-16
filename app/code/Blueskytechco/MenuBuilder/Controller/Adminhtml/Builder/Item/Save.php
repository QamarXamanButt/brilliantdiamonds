<?php
/**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Blueskytechco\MenuBuilder\Controller\Adminhtml\Builder\Item;

use Magento\Framework\Controller\ResultFactory;
use Blueskytechco\MenuBuilder\Controller\Adminhtml\AbstractMenu;
use Magento\Framework\Exception\LocalizedException;

class Save extends AbstractMenu
{
    
    public function execute()
    {
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $error = 0;
        $resourceItemMeta = $this->menuBuilderItemMetaFactory->create()->getResource();
        $data_items = $this->getRequest()->getPost();
        
        if (isset($data_items['menu_item_id']) && isset($data_items['menu_item_id'])) {
            $menu_item_id = $data_items['menu_item_id'];
            unset($data_items['menu_item_id']);
            unset($data_items['form_key']);
            if ($data_items['submenu_type'] == 'default_dropdown') {
                $data_items['submenu_columns'] = null;
                $data_items['submenu_bg_image'] = null;
                $data_items['background_repeat'] = null;
                $data_items['background_position'] = null;
                $data_items['background_size'] = null;
                $data_items['block_content'] = null;
            } elseif ($data_items['submenu_type'] == 'multicolumn_dropdown') {
                $items['block_content'] = null;
                $data_items['full_width_multicolunm'] = ( isset($data_items['full_width_multicolunm']) && $data_items['full_width_multicolunm'] )
                    ? $data_items['full_width_multicolunm'] : null;
                $data_items['dynamic_content_mul'] = ( isset($data_items['dynamic_content_mul']) && $data_items['dynamic_content_mul'] )
                    ? $data_items['dynamic_content_mul'] : null;
            } else {
                $data_items['full_width_block_content'] = ( isset($data_items['full_width_block_content']) && $data_items['full_width_block_content'] )
                    ? $data_items['full_width_block_content'] : null;
                $data_items['dynamic_block_content'] = ( isset($data_items['dynamic_block_content']) && $data_items['dynamic_block_content'] )
                    ? $data_items['dynamic_block_content'] : null;
                $data_items['submenu_columns'] = null;
                $data_items['submenu_bg_image'] = null;
                $data_items['background_repeat'] = null;
                $data_items['background_position'] = null;
                $data_items['background_size'] = null;
            }
            foreach ($data_items as $key_item => $item) {
                $menu_item_meta = $this->menuBuilderItemMetaFactory->create();
                $data_meta = $resourceItemMeta->getDataMeta($menu_item_id, $key_item);
                if ($data_meta) {
                    $menu_item_meta = $menu_item_meta->load($data_meta['entity_id']);
                    $menu_item_meta->setMenuItemId($menu_item_id);
                    $menu_item_meta->setMetaKey($key_item);
                    $menu_item_meta->setMetaValue($item);
                } else {
                    $menu_item_meta->setMenuItemId($menu_item_id);
                    $menu_item_meta->setMetaKey($key_item);
                    $menu_item_meta->setMetaValue($item);
                }
                $menu_item_meta->save();
            }
            $title = $data_items['title'];
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['item_id' => $menu_item_id, 'title' => $title, 'error' => $error]);
        }
        $error = 1;
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['item_id' => '', 'title' => '', 'error' => $error]);
    }
}
