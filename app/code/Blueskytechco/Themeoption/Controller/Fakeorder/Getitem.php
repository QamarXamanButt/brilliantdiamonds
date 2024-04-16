<?php
namespace Blueskytechco\Themeoption\Controller\Fakeorder;

class Getitem extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $request;
	protected $_productCollectionFactory;
	protected $_helper;
	
	public function __construct( 
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\App\Action\Context $context,
		\Blueskytechco\Themeoption\Helper\Themeconfig $helper,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		$this->request = $request;
		$this->_helper = $helper;
		$this->_productCollectionFactory = $productCollectionFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$store = $storeManager->getStore();
		$storeID = $store->getStoreId(); 
		
		$collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->setVisibility(4);
        $collection->addStoreFilter($storeID)->getSelect()->orderRand()->limit(1);
		$html = '';
		$msg = $this->_helper->getInfoFakeOrder('messages');
		
        foreach($collection as $pro){
        	if($pro->getImage() == ''){
        		$img = '//via.placeholder.com/150';
        	}
        	else{
        		$img = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product'.$pro->getImage();	
        	}
			
			$html .= '
				<div class="purchase-image">
					<a class="purchase-img" href="'.$pro->getProductUrl().'">
						<img alt="" class="img_purchase" src="'.$img.'">
					</a>
				</div>
				<div class="purchase-info">
					<span class="dib">'.$msg.'</span>
					<h3 class="title"><a href="'.$pro->getProductUrl().'">'.$pro->getName().'</a></h3>
					<div class="minutes-ago"><span class="minute-number" style=" padding-right: 5px;">'.rand(1,9).'</span> <span>'.__(' minutes ago').'</span></div>
					<a class="btnProductQuickview" href="'.$pro->getProductUrl().'">
					<svg width="12" height="11" viewBox="0 0 12 11" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M10.5 5.03906V5.49609C10.5 6.12109 10.3828 6.70703 10.1484 7.25391C9.91406 7.80078 9.58984 8.27734 9.17578 8.68359C8.76953 9.08984 8.29297 9.41016 7.74609 9.64453C7.19922 9.87891 6.61719 9.99609 6 9.99609C5.375 9.99609 4.78906 9.87891 4.24219 9.64453C3.69531 9.41016 3.21875 9.08594 2.8125 8.67188C2.40625 8.26562 2.08594 7.78906 1.85156 7.24219C1.61719 6.69531 1.5 6.11328 1.5 5.49609C1.5 4.87109 1.61719 4.28906 1.85156 3.75C2.08594 3.20313 2.41016 2.72266 2.82422 2.30859C3.23047 1.90234 3.70703 1.58203 4.25391 1.34766C4.80078 1.11328 5.38281 0.996094 6 0.996094C6.33594 0.996094 6.65625 1.03125 6.96094 1.10156C7.27344 1.17188 7.5625 1.26562 7.82812 1.38281C7.95312 1.44531 8.07812 1.45312 8.20312 1.40625C8.33594 1.35156 8.42969 1.26172 8.48438 1.13672C8.54688 1.01172 8.55078 0.886719 8.49609 0.761719C8.44922 0.628906 8.36328 0.535156 8.23828 0.480469C7.91016 0.324219 7.55469 0.207031 7.17188 0.128906C6.79688 0.0429687 6.40625 0 6 0C5.625 0 5.25781 0.0351562 4.89844 0.105469C4.53906 0.183594 4.19141 0.292969 3.85547 0.433594C3.52734 0.566406 3.21875 0.734375 2.92969 0.9375C2.63281 1.13281 2.35938 1.35547 2.10938 1.60547C1.85938 1.85547 1.63672 2.125 1.44141 2.41406C1.24609 2.71094 1.07812 3.02344 0.9375 3.35156C0.796875 3.67969 0.6875 4.02344 0.609375 4.38281C0.539062 4.74219 0.503906 5.11328 0.503906 5.49609C0.503906 5.87109 0.539062 6.23828 0.609375 6.59766C0.6875 6.95703 0.792969 7.30469 0.925781 7.64062C1.06641 7.96875 1.23828 8.27734 1.44141 8.56641C1.63672 8.86328 1.85938 9.13672 2.10938 9.38672C2.35938 9.63672 2.62891 9.85938 2.91797 10.0547C3.21484 10.2578 3.52734 10.4258 3.85547 10.5586C4.18359 10.6992 4.52734 10.8086 4.88672 10.8867C5.24609 10.957 5.61719 10.9922 6 10.9922C6.375 10.9922 6.74219 10.957 7.10156 10.8867C7.46094 10.8086 7.80469 10.6992 8.13281 10.5586C8.46875 10.4258 8.78125 10.2578 9.07031 10.0547C9.36719 9.85938 9.64062 9.63672 9.89062 9.38672C10.1406 9.13672 10.3633 8.86719 10.5586 8.57812C10.7539 8.28125 10.9219 7.96875 11.0625 7.64062C11.2031 7.3125 11.3125 6.96875 11.3906 6.60938C11.4609 6.25 11.4961 5.87891 11.4961 5.49609V5.03906C11.4961 4.89844 11.4453 4.78125 11.3438 4.6875C11.25 4.58594 11.1367 4.53516 11.0039 4.53516C10.8633 4.53516 10.7422 4.58594 10.6406 4.6875C10.5469 4.78125 10.5 4.89844 10.5 5.03906ZM10.6523 1.14844L6 5.80078L4.85156 4.65234C4.75781 4.55078 4.64062 4.5 4.5 4.5C4.35938 4.5 4.24219 4.55078 4.14844 4.65234C4.04688 4.74609 3.99609 4.86328 3.99609 5.00391C3.99609 5.14453 4.04688 5.26172 4.14844 5.35547L5.64844 6.85547C5.74219 6.95703 5.85938 7.00781 6 7.00781C6.14062 7.00781 6.25781 6.95703 6.35156 6.85547L11.3555 1.85156C11.4492 1.75781 11.4961 1.64062 11.4961 1.5C11.4961 1.35938 11.4492 1.24219 11.3555 1.14844C11.2539 1.04688 11.1328 0.996094 10.9922 0.996094C10.8594 0.996094 10.7461 1.04688 10.6523 1.14844Z" fill="#111111"></path>
					</svg>
						'.__('Verify').'
					</a>
				</div>
			';
		}
		echo json_encode(array('html' => $html));
		die;
	}

}
