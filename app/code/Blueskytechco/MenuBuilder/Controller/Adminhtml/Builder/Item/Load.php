<?php
/**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Blueskytechco\MenuBuilder\Controller\Adminhtml\Builder\Item;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Json\Helper\Data as DataHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Cms\Model\PageFactory;
use Blueskytechco\MenuBuilder\Helper\Admin as MenuHelper;

class Load extends \Magento\Backend\App\Action
{
    
    private $dataHelper;
    
    private $_jsonEncoder;
    
    protected $customerSession;
    
    protected $dataObjectFactory;
    
    protected $_storeManager;
    
    protected $_storeRepository;
    
    protected $pageFactory;
    
    /**
     * @var \Blueskytechco\MenuBuilder\Model\MenuBuilderItemFactory
     */
    protected $menuBuilderItemFactory;
    
    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $_categoryRepository;
    
    /**
     * @var MenuHelper
     */
    protected $menuHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        DataObjectFactory $dataObjectFactory,
        StoreRepositoryInterface $storeRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        DataHelper $dataHelper,
        MenuHelper $menuHelper,
        PageFactory $pageFactory,
        \Blueskytechco\MenuBuilder\Model\MenuBuilderItemFactory $menuBuilderItemFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository
    ) {
        $this->dataHelper = $dataHelper;
        $this->customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_storeRepository = $storeRepository;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->_jsonEncoder = $jsonEncoder;
        $this->menuHelper = $menuHelper;
        $this->pageFactory = $pageFactory;
        $this->_categoryRepository = $categoryRepository;
        $this->menuBuilderItemFactory = $menuBuilderItemFactory;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $id_item = $this->getRequest()->getPost('id_item');
        $type = $this->getRequest()->getPost('type');
        $depth = $this->getRequest()->getPost('depth');
        $parent_id = $this->getRequest()->getPost('parent_id');
        $data_db_id = $this->getRequest()->getPost('data_db_id');
        $html = '';
        $error = 0;
        if (!$id_item) {
            $error = 1;
            return $html;
        }
        $html = $this->menuHelper->getMenuItemSettingsContentHtml(
            $id_item,
            $type,
            $depth,
            $data_db_id,
            $parent_id
        );
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['html' => $html, 'error' => $error]);
    }
}
