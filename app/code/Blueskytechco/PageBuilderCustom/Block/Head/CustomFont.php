<?php
namespace Blueskytechco\PageBuilderCustom\Block\Head;

use Magento\Framework\View\Element\Template;
 
class CustomFont extends Template
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepository;

    /**
     * @var \Magento\Store\Model\ScopeInterface
     */
    protected $_scopeConfig;
 
    /**
     * Header constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->assetRepository = $context->getAssetRepository();
    }

    /**
     * @return array
     */
    public function getCustomFont()
    {
        $array_font = [];

        if ($this->getConfigCustomFont('font_family', 'custom_font_family')) {
            $array_font[] = $this->getConfigCustomFont('font_family', 'custom_font_family');
        }

        if ($this->getConfigCustomFont('heading_family', 'custom_heading_family')) {
            $array_font[] = $this->getConfigCustomFont('heading_family', 'custom_heading_family');
        }

        if ($this->getConfigCustomFont('menu_family', 'custom_menu_family')) {
            $array_font[] = $this->getConfigCustomFont('menu_family', 'custom_menu_family');
        }

        if ($this->getConfigCustomFont('other_family', 'custom_other_family')) {
            $array_font[] = $this->getConfigCustomFont('other_family', 'custom_other_family');
        }

        $array_font = array_unique($array_font);
        $url_font = [];

        foreach($array_font as $font) {
            $url_font[] = $this->getCustomFontUrl($font);
        }

        return $url_font;
    }

    
 
    /**
     * @return string
     */
    public function getConfigFont($font)
    {
        $value = $this->_scopeConfig->getValue('themeoption/font/'.$font.'', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $value;
    }

    /**
     * @return string
     */
    public function getConfigCustomFont($font_family, $font_custom)
    {
        $config_font = null;
        $font_family = $this->getConfigFont($font_family);
        $custom_font_family = $this->getConfigFont($font_custom);
        if ($font_family && $font_family == 'custom' && $custom_font_family) {
            $config_font = str_replace(" ","-",strtolower($custom_font_family));
        }
        return $config_font;
    }

    /**
     * @return string
     */
    public function getCustomFontUrl($font)
    {
        $asset_repository = $this->assetRepository;
        $asset  = $asset_repository->createAsset('Blueskytechco_PageBuilderCustom::css/font-'.$font.'.min.css');
        $url    = $asset->getUrl();
        return $url;
    }
}
