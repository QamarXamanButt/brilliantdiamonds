<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Blueskytechco\CustomCatalog\ViewModel;

use Blueskytechco\CustomCatalog\Helper\Data as Helper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use \Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Framework\View\LayoutInterface;

class Catalog implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Blueskytechco\WidgetCategory\Model\ImageUploader
     */
    protected $imageUploader;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $_stockState;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    
    /**
     * @var GetSalableQuantityDataBySku
     */
    protected $getSalableQuantityDataBySku;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Session
     */
    protected $_customerSession;

    protected $stockState;

    protected $layout;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Blueskytechco\WidgetCategory\Model\ImageUploader $imageUploader,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        PriceCurrencyInterface $priceCurrency,
        \Magento\CatalogInventory\Api\StockStateInterface $_stockState,
        \Magento\Catalog\Helper\Image $imageHelper,
        Registry $registry,
        Helper $helper,
        \Magento\Customer\Model\Session $session,
        ObjectManager $objectManager,
        LayoutInterface $layout
    ) {
        $this->stockState = $_stockState;
        $this->storeManager = $storeManager;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        $this->imageUploader = $imageUploader;
        $this->helper = $helper;
        $this->priceCurrency = $priceCurrency;
        $this->registry = $registry;
        $this->imageHelper = $imageHelper;
        $this->_objectManager = $objectManager;
        $this->layout = $layout;
        $this->_customerSession = $session;
    }

    /**
     * return data config Admin
     */
    public function getDataThemeSetting($config)
    {
        $config = $this->helper->getData('themesetting/'.$config);
        return $config;
    }

    
    /**
     * return data config Admin
     */
    public function getDataQuickView($config)
    {
        $config = $this->helper->getData('quickview_product/general/'.$config);
        return $config;
    }

    /**
     * return params url
     */
    public function getParams()
    {
        $params = $this->request->getParams();
        return $params;
    }

    /**
     * return params buy now
     */
    public function getParamsBuyNow()
    {
        $params = $this->request->getParams();
        $buy_now = '';
        if($this->helper->getData('themesetting/product/enable_buy_now_button') == 'enable'){
            $buy_now = 'buy-now';
        }
        if(isset($params['cart']) && $params['cart'] == 'buy-now'){
            $buy_now = $params['cart'];
        }
        return $buy_now;
    }

    /**
     * return params sticky
     */
    public function getParamsProductSticky()
    {
        $params = $this->request->getParams();
        $sticky = (isset($params['sticky']) && $params['sticky'] == 'enable')
            ? $params['sticky']
            : '';
        if($this->helper->getData('themesetting/product/sticky_add_to_cart') == 'enable'){
            $sticky = 'enable';
        }
        
        return $sticky;
    }

    /**
     * return setting load more
     */
    public function getClassButtonLoadMore()
    {
        $params = $this->request->getParams();

        $add_class_button_lm = '';
        if($this->helper->getData('themesetting/category/load_more_ajax') != ''){
            $page_load_more = $this->helper->getData('themesetting/category/load_more_ajax');
            $add_class_button_lm = ($page_load_more == 'scroll')
                ? ' load-on-scroll'
                : '';
        }
        $add_class_button_lm = (isset($params['load-more']) && $params['load-more'] == 'scroll')
            ? ' load-on-scroll'
            : $add_class_button_lm;
        
        return $add_class_button_lm;
    }

    /**
     * return params sticky
     */
    public function getParamsProductBrand()
    {
        $params = $this->request->getParams();
        $sticky = (isset($params['brand']) && $params['brand'] == 'enable')
            ? $params['brand']
            : '';
        return $sticky;
    }

    /**
     * return sub category html
     */
    public function getSubCategoryHtml()
    {
        $params = $this->getParams();
        $view_type = '';
        $ratio = '';
        if($this->helper->getData('themesetting/category/enable_sub_cat')){
            $view_type = 'sub';
            $ratio = $this->helper->getData('themesetting/category/sub_cat_ratio');
        }
        $ratio = $ratio ? $ratio :0.739496;
        
        if(isset($params['view-type']) && $params['view-type'] == 'sub'){
            $view_type = 'sub';
        }
        $html = '';
        $category = $this->registry->registry('current_category');
        if (!$category || $view_type !== 'sub') {
            return $html;
        }
        $catId = $category->getId();
        $category = $this->categoryRepository->get($catId);
        $subCats = $category->getChildrenCategories();
        if (count($subCats) == 0) {
            return $html;
        }
        
        $html .= '<div class="sub-category widget-category-thumbnail-image" data-col-xxl="5" data-col-xl="5" data-col-lg="4" data-col-md="3" data-col-sm="2" data-col-xs="2">';
            $html .= '<div class="sub-category-slide first-loading-slick">';
            foreach ($subCats as $subcat) {
                $sub_category = $this->categoryRepository->get($subcat->getId());
                $subcaturl = $sub_category->getUrl();
                $_imgUrl = '';
                if ($sub_category->getData('widget_category_thumbnail')) {
                    $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
                    $_imgUrl = $mediaUrl.$this->imageUploader->getBasePath().'/'.$sub_category->getData('widget_category_thumbnail');
                } else {
                    $_imgUrl = '//blueskytechmage.com/place_holder/kalles/place_holder-backpack_1.png';
                }
                $html .= '<div class="elementor-category-thumbnail-image-item item-loading-slick">';
                    $html .= '<div class="elementor__item--hover">';
                        if ($_imgUrl) {
                            $html .= '<a
                                href="'.$subcaturl.'"
                                class="category-thumbnail__image data-bgset-image-wrapper lazyload"
                                style="--aspect-ratio: '.$ratio.'"
                                data-bgset="'.$_imgUrl.'"
                                data-sizes="auto"
                            >
                            </a>';
                        }
                    $html .= '</div>';
                    $html .= '<div class="category-thumbnail__info absolute flex-layout bottom justify-content-between align-items-center">';
                        $html .= '<div class="category-info-wrapper">';
                            $html .= '<h3 class="category-thumbnail__title">';
                                $html .= '<a class="btn category-thumbnail__title_as_button" href="'.$subcaturl.'"> '.$sub_category->getName().' </a>';
                            $html .= '</h3>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    public function getStockQty($productId, $websiteId = null)
    {
        return $this->stockState->getStockQty($productId, $websiteId);
    }

    public function getProductSalableQuantity($pro)
    {
        $sale_qty = 0;
        if($pro->getTypeId() == 'simple' || $pro->getTypeId() == 'virtual'){
            $qty = $this->getStockQty($pro->getId());
            $salableqty = $this->getSalableQuantityDataBySku->execute($pro->getSku());
            if($qty && $qty > 0){
                $sale_qty = isset($salableqty[0]['qty']) ? $salableqty[0]['qty'] : 0;
                return $sale_qty;
            }
        }
        return $sale_qty;
    }

    public function getProductSalableQuantityHtml($pro)
    {
        $html = '';
        if($pro->getTypeId() == 'simple' || $pro->getTypeId() == 'virtual'){
            $qty = $this->getStockQty($pro->getId());
            $salableqty = $this->getSalableQuantityDataBySku->execute($pro->getSku());
            if($qty && $qty > 0){
                $sale_qty = isset($salableqty[0]['qty']) ? $salableqty[0]['qty'] : 0;
                $percent = ($sale_qty / $qty) * 100;
                $html .= '<div class="bls__product-stock-countdow mb-25">';
                    $html .= '<div class="product_progress_countdow">';
                        $html .= '<p class="mb-10">Hurry Up! Only <span class="bls__count primary-color heading-weight">'.$sale_qty.'</span> left in stock!</p>';
                        $html .= '<div class="progressbar">';
                            $html .= '<div class="progressbar-stock" style="width: '.$percent.'%;"></div>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            }
        }
        return $html;
    }

    public function getFormatedPrice($price)
    {
        return $this->priceCurrency->convertAndFormat($price);
    }

    public function getActionZoom()
    {
        $params = $this->request->getParams();
        $zoom = '';
        if($this->helper->getData('themesetting/product/images_zoom') != ''){
            $zoom = $this->helper->getData('themesetting/product/images_zoom');
        }
        if(isset($params['zoom']) && $params['zoom']){
            $zoom = $params['zoom'];
        }
        return $zoom;
    }

    public function getThumbImage()
    {
        $params = $this->request->getParams();
        $thumb = '';
        if($this->helper->getData('themesetting/product/display_thumbnails') != ''){
            $thumb = $this->helper->getData('themesetting/product/display_thumbnails');
        }
        if(isset($params['thumb']) && $params['thumb']){
            $thumb = $params['thumb'];
        }
        return $thumb;
    }

    public function getGalleryType()
    {
        $params = $this->request->getParams();
        $gallery_type = '';
        if($this->helper->getData('themesetting/product/enable_photoswipe_popup') == 'enable'){
            $gallery_type = 'pswp';
        }
        if(isset($params['gallery-type']) && $params['gallery-type']){
            $gallery_type = $params['gallery-type'];
        }
        return $gallery_type;
    }

    public function getImageHelper()
    {
        return $this->imageHelper;
    }

    public function getVideoUrl($url)
    {
        if (strpos($url, 'youtube') !== false) {
            $url_array = explode('v=',$url);
            if(isset($url_array[1]) && $url_array[1]){
                $id = explode('&', $url_array[1]);
                if (isset($id[0]) && $id[0]) {
                    $url = 'https://www.youtube.com/embed/'.$id[0].'';
                    return $url;
                }
            }
        }
        return false;
    }

    public function getBundleImage($selection)
    {
        $product_id = $selection->getProductId();
        $_product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
        $imageBlock = $this->layout->createBlock(\Magento\Catalog\Block\Product\View::class);
        $imageBlock->setDataProduct($_product)
            ->setTemplate('Blueskytechco_CustomCatalog::product/image.phtml');
        $htmlImage = $imageBlock->toHtml();
        return $htmlImage;
    }

    public function getCustomerLogged()
    {
        if ($this->_customerSession->isLoggedIn()) {
            $customer = $this->_customerSession->getCustomer();
        } else {
            $customer = false;
        }
        return $customer; 
    }
}
