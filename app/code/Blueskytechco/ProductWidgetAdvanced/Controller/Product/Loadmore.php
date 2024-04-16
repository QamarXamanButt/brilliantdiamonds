<?php
namespace Blueskytechco\ProductWidgetAdvanced\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\LayoutInterface;

class Loadmore extends Action
{
    private $resultJsonFactory;
    protected $_storeManager;

    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context, 
        LayoutInterface $layout,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ){
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_storeManager = $storeManager;
        $this->layout = $layout;
    }
 
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        $block = $this->layout->createBlock('Blueskytechco\ProductWidgetAdvanced\Block\Product\LoadMore');
        $block->setDataBlock($data)->setTemplate('Blueskytechco_ProductWidgetAdvanced::widget/product_advanced/grid/default.phtml');
        $html = $block->toHtml();
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($html);
    }
}