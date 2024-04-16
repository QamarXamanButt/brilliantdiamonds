<?php

namespace Blueskytechco\Themeoption\Block\Html;

use Magento\Framework\App\Filesystem\DirectoryList;

class Header extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $reader;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_directory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Module\Dir\Reader $reader
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Module\Dir\Reader $reader,
        array $data = []
    ) {
        $this->readFactory = $readFactory;
        $this->reader = $reader;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::APP);
        parent::__construct($context, $data);
    }

    public function getConfig($config_path)
    {
        return $this->_scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isEnableTopBar()
    {
        return $this->getConfig('themesetting/header/enable_topbar');
    }

    public function _toHtml()
    {
        $header_config = $this->_scopeConfig->getValue(
            'themesetting/header/select_header_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if($header_config && $header_config != ''){
            $templateFile = 'Magento_Theme::html/headers/'.$header_config.'.phtml';
            $file = $this->resolver->getTemplateFileName($templateFile, ['module' => 'Magento_Theme']);
            if ($file) {
                $this->setTemplate($templateFile);
            } else {
                $this->setTemplate('Magento_Theme::html/header_custom.phtml');
            }
        }
        else{
            $this->setTemplate('Magento_Theme::html/header_custom.phtml');
        }
        
        return parent::_toHtml();
    }
}
