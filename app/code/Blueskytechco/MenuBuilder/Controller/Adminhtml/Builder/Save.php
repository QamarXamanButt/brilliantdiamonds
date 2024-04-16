<?php
/**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Blueskytechco\MenuBuilder\Controller\Adminhtml\Builder;

use Blueskytechco\MenuBuilder\Controller\Adminhtml\AbstractMenu;
use Magento\Framework\Exception\LocalizedException;

/**
 * Menu Builder Save
 *
 */
class Save extends AbstractMenu
{
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        $menus = $this->menuBuilderFactory->create();
        $menu_item = $this->menuBuilderItemFactory->create();
        $resourceItem = $menu_item->getResource();
        if ($data) {
            if (isset($data['entity_id']) && $data['entity_id']) {
                $entity_id = $data['entity_id'];
                try {
                    $menus = $menus->load($entity_id);
                    $menus->setName($data['menu-name']);
                    $menus->setType($data['menu-type']);
                    $menus->save();
                    $resourceItem->updateStatusItem($data['entity_id']);
                    if (isset($data['menu-item'])) {
                        $menu_items = $data['menu-item'];
                        $this->saveMenuItem($menu_items);
                    }
                    $resourceItem->getDeteteItem($data['entity_id']);
                    $this->messageManager->addSuccessMessage(__('The menus has been saved.'));
                    return $resultRedirect->setPath('*/*/edit', ['id' => $entity_id, '_current' => true]);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $message = __('Something went wrong while saving the menus.');
                    $this->messageManager->addExceptionMessage($e, $message);
                }
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            } else {
                if (isset($data['menu-name']) && isset($data['menu-type'])
                    && $data['menu-name'] && $data['menu-type']) {
                    try {
                        $menus->setIdentifier(time().rand());
                        $menus->setName($data['menu-name']);
                        $menus->setType($data['menu-type']);
                        $menus->save();
                        $this->messageManager->addSuccessMessage(__('Create menus Success.'));
                        return $resultRedirect->setPath('*/*/edit', ['id' => $menus->getEntityId()]);
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    } catch (\RuntimeException $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    } catch (\Exception $e) {
                        $message = __('Something went wrong while saving the menus.');
                        $this->messageManager->addExceptionMessage($e, $message);
                    }
                } else {
                    $message = __('Please enter full information so you can create menus.');
                    $this->messageManager->addErrorMessage($message);
                    return $resultRedirect->setPath('*/*/new');
                }
            }
        }
        return $resultRedirect->setPath('menus/builder');
    }
    
    public function saveMenuItem($menu_items)
    {
        $menu_item = $this->menuBuilderItemFactory->create();
        $resourceItem = $this->menuBuilderItemFactory->create()->getResource();
        $resourceItemMeta = $this->menuBuilderItemMetaFactory->create()->getResource();
        $i=1;
        foreach ($menu_items as $key => $items) {
            $id_item = $key;
            $resourceItem->updateMenuItem($id_item, $items['title'], $i, 1);
            if (isset($items['parent_id'])) {
                $resourceItemMeta->updateParentItemMeta($id_item, $items['parent_id']);
            }
            $i++;
        }
    }
}
