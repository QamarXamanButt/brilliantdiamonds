<?php

namespace Criteo\OneTag\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Description of Index
 *
 * @author Criteo
 */
class Index extends \Magento\Framework\App\Action\Action {

    protected $_resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory) {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute() {
        return $this->_resultPageFactory->create();
    }

}
