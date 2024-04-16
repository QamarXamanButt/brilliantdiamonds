<?php
/**
 * Copyright © 2021 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Blueskytechco\SearchSuite\Model\Search;

use \Magento\Framework\ObjectManagerInterface as ObjectManager;
use \Magento\Search\Model\QueryFactory;
use \Magento\Catalog\Model\Layer\Resolver;
use \Blueskytechco\SearchSuite\Helper\Data as SearchHelper;
use \Magento\Framework\UrlInterface;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\View\LayoutInterface;
/**
 * Product model. Return product data used in search autocomplete
 */
class Product implements \Blueskytechco\SearchSuite\Model\SearchInterface
{
	/**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $_layerResolver;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Search\Helper\Data
     */
    protected $_searchHelper;
	
	/**
     * @var \Magento\Framework\App\RequestInterface
     */
	protected $request;
	
	/**
     * @var \Magento\Framework\UrlInterface
     */
	protected $urlBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
	protected $_productCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $categoryRepository;
    protected $layout;

    public function __construct(
        Resolver $layerResolver,
        ObjectManager $objectManager,
        QueryFactory $queryFactory,
        SearchHelper $searchHelper,
		UrlInterface $urlBuilder,
        CollectionFactory $_productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
		\Magento\Framework\App\RequestInterface $request,
        LayoutInterface $layout
    ) {
        $this->_layerResolver = $layerResolver;
        $this->_objectManager = $objectManager;
        $this->_queryFactory = $queryFactory;
        $this->_searchHelper = $searchHelper;
		$this->urlBuilder = $urlBuilder;
        $this->_productCollectionFactory = $_productCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
		$this->request = $request;
        $this->layout = $layout;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseData()
    {
        $queryText = $this->_queryFactory->get()->getQueryText();
		$queryCat = $this->request->getParam('cat');
		$productCollection = $this->getProductCollection($queryText,$queryCat);
		foreach ($productCollection as $product) {
			$responseData['data'][] = $this->getProductData($product);
		}
		$responseData['size'] = (count($productCollection) > 0) ? $productCollection->getSize() : 0;
        $responseData['text_suggestion'] = (count($productCollection) > 1) ? __('Search Results:') : __('Search Result:');
		$responseData['url'] = (count($productCollection) > 0) ? $this->_getUrl('catalogsearch/result',['_query' => ['q' => $queryText,'cat'=>$queryCat], '_secure' => $this->request->isSecure()]) : '';
		return $responseData;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseDataSuggestProduct()
    {
        $text_suggestion = $this->_searchHelper->getConfigData('searchsuite/product_suggestion/text_suggestion');
        $category_id = $this->request->getParam('cate');
        $category = $this->categoryRepository->get($category_id, $this->_storeManager->getStore()->getId());
		$productCollection = $this->getProductCollectionByCategory($category_id);
		foreach ($productCollection as $product) {
			$responseData['data'][] = $this->getProductData($product);
		}
		$responseData['size'] = (count($productCollection) > 0) ? $productCollection->getSize() : 0;
        $responseData['text_suggestion'] = $text_suggestion?$text_suggestion:__('Need some inspiration?');
        $responseData['url'] =  $category?$category->getUrl(): '#';
		return $responseData;
    }
	
	public function getProductCollection($queryText,$queryCat = null)
    {
        $limit = $this->_searchHelper->getConfigData('searchsuite/search/limit');
    	$limit = $limit?$limit:5;
        $this->_layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
    	$productCollection = $this->_layerResolver->get()
            ->getProductCollection() 
			->addAttributeToSelect('*');
		if($queryCat){
			$productCollection->addCategoriesFilter(array('in' => [$queryCat]));
		}	
        $productCollection->getSelect()->limit($limit);
        return $productCollection;
    }

    public function getProductCollectionByCategory($category_id)
    {
        $limit = $this->_searchHelper->getConfigData('searchsuite/search/limit');
    	$limit = $limit?$limit:5;
        $category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($category_id);
    	$productCollection = $this->_productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');
        if ($category) {
            $productCollection->addCategoryFilter($category);
        }
        $productCollection->getSelect()->limit($limit);
        return $productCollection;
    }

    private function getProductData($product)
    {
    	/**
    	* @var \Blueskytechco\SearchSuite\Block\Product $product
    	*/
        $_product = $this->_objectManager->create('Blueskytechco\SearchSuite\Block\Product\ProductAggregator')->setProduct($product);
        $imageBlock = $this->layout->createBlock(\Blueskytechco\SearchSuite\Block\Product::class);
        $imageBlock->setProduct($_product->getProduct())
            ->setTemplate('Blueskytechco_SearchSuite::product/image.phtml');
        $htmlImage = $imageBlock->toHtml();
        $productData = [
            'name' => $_product->getName(),
            'url' => $_product->getUrl() 
        ];
		if($this->_searchHelper->getConfigData('searchsuite/product/enable_image')){
            $productData['image'] = $htmlImage;
        }
        if($this->_searchHelper->getConfigData('searchsuite/product/enable_brand')){
            $attribute = $product->getResource()->getAttribute('brand');
            $brand = '';
            if ($attribute) {
                $brand = $attribute->getFrontend()->getValue($product);
            }
            $productData['brand'] = $brand;
        }
		if($this->_searchHelper->getConfigData('searchsuite/product/enable_short_description')){
            $productData['short_description'] = $_product->getShortDescription();
        }
        if($this->_searchHelper->getConfigData('searchsuite/product/enable_reviews_rating')){
			$productData['reviews_rating'] = $_product->getReviewsRating();
        }
		if($this->_searchHelper->getConfigData('searchsuite/product/enable_price')){
			$productData['price'] = $_product->getPrice();
        }
        return $productData;
    }
	
	protected function _getUrl($route, $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}