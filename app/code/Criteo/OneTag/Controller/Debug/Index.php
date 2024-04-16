<?php

namespace Criteo\OneTag\Controller\Debug;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Index extends \Magento\Framework\App\Action\Action {

    const SETTINGS_VERSION = 'cto_onetag_section/general/cto_version';
    const SETTINGS_PARTNER_ID = 'cto_onetag_section/general/cto_partner';
    const SETTINGS_ENABLE_HOME = 'cto_onetag_section/general/cto_enable_home';
    const SETTINGS_ENABLE_LISTING = 'cto_onetag_section/general/cto_enable_listing';
    const SETTINGS_ENABLE_PRODUCT = 'cto_onetag_section/general/cto_enable_product';
    const SETTINGS_ENABLE_BASKET = 'cto_onetag_section/general/cto_enable_basket';
    const SETTINGS_ENABLE_SALE = 'cto_onetag_section/general/cto_enable_sale';
    const SETTINGS_USE_SKU = 'cto_onetag_section/general/cto_use_sku';

    protected $_scopeConfig;

    public function __construct(Context $context,
            ScopeConfigInterface $scopeConfig) {

        $this->_scopeConfig = $scopeConfig;

        parent::__construct($context);
    }

    public function execute() {
        //get configuration info
        $version = $this->_scopeConfig->getValue(self::SETTINGS_VERSION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $partner_id = $this->_scopeConfig->getValue(self::SETTINGS_PARTNER_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enable_home = $this->_scopeConfig->getValue(self::SETTINGS_ENABLE_HOME, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enable_listing = $this->_scopeConfig->getValue(self::SETTINGS_ENABLE_LISTING, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enable_product = $this->_scopeConfig->getValue(self::SETTINGS_ENABLE_PRODUCT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enable_basket = $this->_scopeConfig->getValue(self::SETTINGS_ENABLE_BASKET, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enable_sale = $this->_scopeConfig->getValue(self::SETTINGS_ENABLE_SALE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $use_sku = $this->_scopeConfig->getValue(self::SETTINGS_USE_SKU, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $html = <<<EOT
            <h3>CRITEO MODULE DEBUG <i>(module version $version )</i></h3>
            <b>TAG RELATED PARAMS</b><br/>
            Account ID = <b>$partner_id</b><br/>
            Use SKU for Product ID? = <b>$use_sku</b><br/>
            Tag Homepage activated = <b>$enable_home</b><br/>
            Tag Listing activated = <b>$enable_listing</b><br/>
            Tag Product activated = <b>$enable_product</b><br/>
            Tag Basket activated = <b>$enable_basket</b><br/>
            Tag Sales activated = <b>$enable_sale</b><br/>
EOT;
        echo $html;
    }

}
