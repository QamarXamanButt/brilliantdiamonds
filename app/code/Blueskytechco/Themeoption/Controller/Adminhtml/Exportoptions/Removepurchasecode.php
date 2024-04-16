<?php

namespace Blueskytechco\Themeoption\Controller\Adminhtml\Exportoptions;

use Magento\Framework\App\PageCache\Version;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;


class Removepurchasecode extends \Magento\Backend\App\Action
{
    protected $_config;
    protected $cacheTypeList;
    protected $cacheFrontendPool;
    protected $indexFactory;
    protected $indexCollection;
    protected $_verifypurchasecode;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config,
        \Magento\Indexer\Model\IndexerFactory $indexFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexCollection,
        \Blueskytechco\Themeoption\Helper\Verifypurchasecode $verifypurchasecode,
        TypeListInterface $cacheTypeList, 
        Pool $cacheFrontendPool
    ) {
        parent::__construct($context);
        $this->_config = $config;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->indexFactory = $indexFactory;
        $this->indexCollection = $indexCollection;
        $this->_verifypurchasecode = $verifypurchasecode;
    }

    public function execute()
    {
        $scope = 'default';
        $scope_id = 0;
        $this->_verifypurchasecode->removeEnvatoPurchaseCode();
        $this->_config->saveConfig('activationcode/activation/purchasecode','',$scope,$scope_id);
        $this->_config->saveConfig('activationcode/activation/purchasecode_confirm','',$scope,$scope_id);

        $_types = [
            'config',
            'layout',
            'block_html',
            'full_page'
        ];

        foreach ($_types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }

        $indexidarray = $this->indexFactory->create()->load('design_config_grid');
        $indexidarray->reindexAll('design_config_grid');
        $this->messageManager->addSuccess(__('Remove Activation Purchase Code Successful!'));
        $this->_redirect('adminhtml/system_config/edit/section/activationcode');
    }
}
?>