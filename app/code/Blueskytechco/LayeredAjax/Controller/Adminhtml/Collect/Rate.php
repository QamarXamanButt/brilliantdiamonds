<?php
namespace Blueskytechco\LayeredAjax\Controller\Adminhtml\Collect;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Rate extends Action
{
    protected $resultPageFactory;
    protected $_cronJobObject;

    public function __construct(
        Context $context,
        \Blueskytechco\LayeredAjax\Cron\Collect\Rate $object
    ) {
        $this->_cronJobObject = $object;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->_cronJobObject->execute();
        $this->messageManager->addSuccess(__('Collect Product Data Rate Successfully.'));
        $this->_redirect('adminhtml/system_config/edit/section/layered_ajax');
    }
}
