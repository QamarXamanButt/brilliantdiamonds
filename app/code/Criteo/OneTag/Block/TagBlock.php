<?php

namespace Criteo\OneTag\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Criteo\OneTag\Helper\TagGenerator;
use Magento\Framework\ObjectManagerInterface;

/**
 * Description of TagBlock
 *
 * @author Criteo
 */
class TagBlock extends \Magento\Framework\View\Element\Template
{
    const SETTINGS_PARTNER_ID = 'cto_onetag_section/general/cto_partner';
    const SETTINGS_ENABLE_HOME = 'cto_onetag_section/general/cto_enable_home';
    const SETTINGS_ENABLE_LISTING = 'cto_onetag_section/general/cto_enable_listing';
    const SETTINGS_ENABLE_PRODUCT = 'cto_onetag_section/general/cto_enable_product';
    const SETTINGS_ENABLE_BASKET = 'cto_onetag_section/general/cto_enable_basket';
    const SETTINGS_ENABLE_SALE = 'cto_onetag_section/general/cto_enable_sale';
    const SETTINGS_ENABLE_LOADER = 'cto_onetag_section/general/cto_enable_loader';
    const SETTINGS_ENABLE_EEC_DL = 'cto_onetag_section/general/cto_enable_eec_dl';
    const SETTINGS_USE_SKU = 'cto_onetag_section/general/cto_use_sku';

    protected $_request;
    protected $logger;
    protected $_registry;
    protected $_scopeConfig;
    protected $_customerSession;
    protected $_cart;
    protected $_order;
    protected $_tagGenerator;
    protected $_useSku;
    protected $_objectManager;
    protected $_storeManager;
	protected $_productloader;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger,
        Http $request,
        Registry $registry,
        \Magento\Customer\Model\Session $customer_session,
        \Magento\Checkout\Model\Session $cart,
        Order $order,
        TagGenerator $tag_generator,
        ObjectManagerInterface $objectManager,
		\Magento\Catalog\Model\ProductFactory $productloader,
        array $data = []
    ) {
        $this->_request = $request;
        $this->_logger = $logger;
        $this->_registry = $registry;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_customerSession = $customer_session;
        $this->_cart = $cart;
        $this->_order = $order;
        $this->_tagGenerator = $tag_generator;
        $this->_useSku = $this->_scopeConfig->getValue(self::SETTINGS_USE_SKU, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_objectManager = $objectManager;
        $this->_storeManager = $context->getStoreManager();
		$this->_productloader = $productloader;

        parent::__construct($context, $data);
    }

    public function cto_generate_tag()
    {
        $output = '';

        //get configuration info
        $partnerId = $this->_scopeConfig->getValue(self::SETTINGS_PARTNER_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enableHome = $this->_scopeConfig->getValue(self::SETTINGS_ENABLE_HOME, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enableListing = $this->_scopeConfig->getValue(self::SETTINGS_ENABLE_LISTING, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enableProduct = $this->_scopeConfig->getValue(self::SETTINGS_ENABLE_PRODUCT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enableBasket = $this->_scopeConfig->getValue(self::SETTINGS_ENABLE_BASKET, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enableSale = $this->_scopeConfig->getValue(self::SETTINGS_ENABLE_SALE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enableLoader = $this->_scopeConfig->getValue(self::SETTINGS_ENABLE_LOADER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enableEecDl = $this->_scopeConfig->getValue(self::SETTINGS_ENABLE_EEC_DL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);



        //set partner id
        $this->_tagGenerator->setAccount(array('account' => $partnerId));

        //get and set the user info if available
        $email = '';
        $zipcode = '';
        if ($this->_customerSession->isLoggedIn()) {
            $email = $this->_customerSession->getCustomer()->getEmail();
            $email = !empty($email) ? hash('sha256', strtolower(trim($email))) : '';
        }

        // if Loader or DL are activated we should pull data. At this stage it doesnt matter if its the loader or the EEC or both thats activated
        if ($enableLoader || $enableEecDl) {

            $this->_tagGenerator->setEmail(array('email' => $email, 'hash_method' => 'sha256'));

            // fire the tags based on page type
            if ($this->is_homepage()) {
                $this->_tagGenerator->viewHome();

                // disabling the loader is like disabling the tag. So we need to check $enableLoader again for each tag
                if ($enableHome && $enableLoader) {
                    $output = $this->_tagGenerator->cto_get_code();
                }
            } elseif ($this->is_listing()) {
                $product_list = $this->get_product_list_data();
                $this->_tagGenerator->viewList($product_list);

                if ($enableListing && $enableLoader) {
                    $output = $this->_tagGenerator->cto_get_code();
                }
            } elseif ($this->is_product()) {
                $product_id = $this->get_product_id();
                $this->_tagGenerator->viewItem($product_id);

                if ($enableProduct && $enableLoader) {
                    $output = $this->_tagGenerator->cto_get_code();
                }
            } elseif ($this->is_cart()) {
                $items = $this->get_cart();
                $this->_tagGenerator->viewBasket($items);

                if ($enableBasket && $enableLoader) {
                    $output = $this->_tagGenerator->cto_get_code();
                }
            } elseif ($this->is_sale()) {
                $this->_tagGenerator->trackTransaction($this->get_sale_data());

                if ($enableSale && $enableLoader) {
                    $output = $this->_tagGenerator->cto_get_code();
                }    
            // generic pages that only have the dynamic loader    
            } elseif ($enableLoader) {
                $output = $this->_tagGenerator->cto_get_code();
            }

            //show data layer only if enabled
            if ($enableEecDl) {
                $output .= $this->_tagGenerator->cto_set_dataLayer();
            }
        }

        return $output;
    }

    private function is_homepage()
    {
        return $this->_request->getFullActionName() == 'cms_index_index';
    }

    private function is_listing()
    {
        return $this->_request->getFullActionName() == 'catalog_category_view';
    }

    private function is_product()
    {
        return $this->_request->getFullActionName() == 'catalog_product_view';
    }

    private function is_cart()
    {
        $action_name = $this->_request->getFullActionName();
        return $action_name =='checkout_cart_index' || $action_name == 'checkout_index_index'  || $action_name == 'checkout_onepage_index' || $action_name == 'firecheckout_index_index';
    }

    private function is_sale()
    {
		 $action_name = $this->_request->getFullActionName();
		 return $action_name == "checkout_onepage_success" || $action_name == "onepagecheckout_index_success";
    }

    private function get_product_id()
    {
        $product_id = '';

        try {
            $current_product = $this->_registry->registry('current_product');
            $product_id = $this->_useSku ?  $current_product->getSku() : $current_product->getId();
        } catch (\Exception $exc) {
            //do nothing
        }
        return array('item' => $product_id);
    }

    private function get_product_list_data()
    {
        $product_list = array();
        $category = "";
        try {
            $products = $this->_registry->registry('current_category')->getProductCollection();
            foreach ($products as $product) {
                $product_list[] = $this->_useSku ? $product->getSku() : $product->getId();
                if (sizeof($product_list) == 3) {
                    break;
                }
            }
            $category = $this->_registry->registry('current_category')->getName();
        } catch (\Exception $exc) {
            //do nothing
        }

        return array('item' => $product_list, 'category' => $category);
    }

    private function get_cart()
    {
        $cart_content = array();
        $currency_code = '';
        try {
            $products = $this->_cart->getQuote()->getAllVisibleItems();
            foreach ($products as $product) {
				$price = (float) $product->getPrice();
				$quantity = (int) $product->getQty();
				$product = $this->get_parent_product_if_any($product);
                $product_id = $this->_useSku ?  $product->getSku() : $product->getId();
                $cart_content[] = array(
                    'id' => $product_id,
                    'price' => $price,
                    'quantity' => $quantity
                );
            }

            $currency_code = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        } catch (\Exception $exc) {
            // do nothing
        }

        return array('item' => $cart_content);
    }

    private function get_sale_data()
    {
        $transaction_id = '';
        $purchased_products = array();
        $currency_code = '';
        $zipcode = '';

        try {
            $order_id = $this->_cart->getLastOrderId();
            $order = $this->_order->load($order_id);

            //get transaction id (known to customer)
            $transaction_id = $order->getIncrementId();

            //get zipcode of shipping address
            $zipcode = $order->getBillingAddress()->getData("postcode");

            //get purchased products
            $products = $order->getAllVisibleItems();
            foreach ($products as $product) {
			    $price = (float) $product->getPrice();
				$quantity = (int) $product->getQtyOrdered();
				$product = $this->get_parent_product_if_any($product);
                $product_id = $this->_useSku ? $product->getSku() : $product->getId();
                $purchased_products[] = array(
                    'id' => $product_id,
                    'price' => $price,
                    'quantity' => $quantity
                );
            }

            $currency_code = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        } catch (\Exception $exc) {
            //do nothing
        }

        return array('id' => $transaction_id, 'currency' => $currency_code, 'item' => $purchased_products, 'zipcode' => $zipcode);
    }
	
	private function get_parent_product_if_any($product) {
		if (!$product) {
            return;
        }

        //use product parent ID if any
        $product_id = $product->getProductId();
		return $this->_productloader->create()->load($product_id);
		
	}
}
