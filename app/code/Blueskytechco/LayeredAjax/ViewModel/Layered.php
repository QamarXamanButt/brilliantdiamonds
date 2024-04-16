<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Blueskytechco\LayeredAjax\ViewModel;

use Blueskytechco\LayeredAjax\Helper\Data as Helper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\ObjectManagerInterface;

class Layered implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductCollection $productCollection,
        ObjectManagerInterface $objectManager,
        Helper $helper,
        Registry $registry
    ) {
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->productCollection = $productCollection;
        $this->objectManager = $objectManager;
    }
    
    public function getPriceRange()
    {
        $category = $this->registry->registry('current_category');
        $ProductFactory = $this->productCollection->addAttributeToSelect('price')->setOrder('price', 'DESC')->addCategoryFilter($category); 
        $maxPrice = $ProductFactory->getMaxPrice();
        $minPrice = $ProductFactory->getMinPrice();
        $filterprice = array('min' => $minPrice, 'max' => $maxPrice);
        return $filterprice;
    }

    public function getCurrencySymbol()
    {
		$currency = $this->objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($this->storeManager->getStore()->getCurrentCurrencyCode());
        return $currency->getCurrencySymbol();
    }

    public function getDataQuickView($config)
    {
        $config = $this->helper->getData('quickview_product/general/'.$config);
        return $config;
    }
    
}
