<?php

namespace Blueskytechco\LayeredAjax\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
	protected $storeManager;
	
	protected $objectManager;

	public function __construct(
		Context $context,
		ObjectManagerInterface $objectManager,
		StoreManagerInterface $storeManager
	)
	{
		$this->objectManager   = $objectManager;
		$this->storeManager    = $storeManager;
		parent::__construct($context);
	}

    /**
     * Return config data
     *
     * @return string
     */
    public function getData($config)
    {
        $config = $this->scopeConfig->getValue($config, ScopeInterface::SCOPE_STORE);
        return $config;
    }
	
	public function isEnabled($storeId = null)
	{
		return $this->scopeConfig->getValue(
			'layered_ajax/general/enable',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}
	public function opendDesktop($storeId = null)
	{
		return $this->scopeConfig->getValue(
			'layered_ajax/general/open_desktop',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}

    public function opendMobile($storeId = null)
	{
		return $this->scopeConfig->getValue(
			'layered_ajax/general/open_mobile',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}

    public function showCountItem($storeId = null)
	{
		return $this->scopeConfig->getValue(
			'layered_ajax/general/show_count_item',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}

    public function isEnabledPriceRangeSliders($storeId = null)
	{
		return $this->scopeConfig->getValue(
			'layered_ajax/price_slider/enable',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}

    public function isEnabledRating($storeId = null)
	{
		return $this->scopeConfig->getValue(
			'layered_ajax/rate_filter/enable',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}

    public function ratingLable($storeId = null)
	{
		return $this->scopeConfig->getValue(
			'layered_ajax/rate_filter/lable',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}

    public function isEnabledLessMore($storeId = null)
	{
		return $this->scopeConfig->getValue(
			'layered_ajax/show_more/enable',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}

    public function numberItem($storeId = null)
	{
		return $this->scopeConfig->getValue(
			'layered_ajax/show_more/number',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}
}
