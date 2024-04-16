<?php

namespace Blueskytechco\Themeoption\Setup;

use Blueskytechco\Themeoption\Model\Googlefonts;
use Blueskytechco\Themeoption\Model\GooglefontsFactory;
use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\PageBuilder\Model\TemplateFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class InstallData implements InstallDataInterface
{
    private $_googlefontsFactory;
    private $state;
    protected $_getFile;
    protected $_geDir;
    protected $_parser;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    public function __construct(
        \Blueskytechco\Themeoption\Model\GooglefontsFactory $googlefontsFactory,
        TemplateFactory $templateFactory,
        \Magento\Framework\Filesystem $file,
        \Magento\Framework\App\State $state
    ) {
        $this->_googlefontsFactory = $googlefontsFactory;
        $this->_parser = new \Magento\Framework\Xml\Parser();
        $this->state = $state;
        $this->_getFile = $file;
        $this->templateFactory = $templateFactory;
        $this->_geDir = $this->_getFile->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/Blueskytechco/Themeoption');
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Exception $e) {
            /* Do nothing, it's OK */
        }

        $header   = [];
        $header[] = 'Content-length: 0';
        $header[] = 'Content-type: application/json; charset=utf-8';

        $verify_url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyDX6p6AZ9epSHfXc1an-HTWOqI6hcFj-kE';
        $ch_verify = curl_init($verify_url);

        curl_setopt( $ch_verify, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $ch_verify, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch_verify, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch_verify, CURLOPT_CONNECTTIMEOUT, 5 );
        curl_setopt( $ch_verify, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $cinit_verify_data = curl_exec($ch_verify);
        curl_close( $ch_verify );
        $result = json_decode($cinit_verify_data, true);
        if(is_array($result) && !empty($result) && isset($result['items'])){
            foreach($result['items'] as $_item){
                if(isset($_item['family'])){
                    $this->_googlefontsFactory->create()->setData(['family' => $_item['family']])->save();
                }
            }
        }

        $files_template = scandir($this->_geDir.'/demo/template');
        foreach($files_template as $f) {
            if ($f != '.' && $f != '..'){
                $xmlPathTemplate = $this->_geDir.'/demo/template/'.$f;
                if (is_readable($xmlPathTemplate))
                {
                    $data_file_import = $this->_parser->load($xmlPathTemplate)->xmlToArray();
                    if(isset($data_file_import['root']['templates'])){
                        $templates_data = $data_file_import['root']['templates'];
                        if (isset($templates_data['item'])) {
                            $items = $templates_data['item'];
                            foreach ($items as $item) {
                                $template = $this->templateFactory->create();
                                $template->setName($item['name']);
                                $template->setTemplate($item['template']);
                                $template->setCreatedFor($item['created_for']);
                                $template->setPreviewImage($item['preview_image']);
                                $template->save();
                            }
                        }
                    }
                }
            }
            
        }
    }
}