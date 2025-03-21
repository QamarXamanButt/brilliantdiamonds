<?php
namespace Blueskytechco\LayeredAjax\Plugins\Model\Layer\Filter;

class Item
{
	protected $_url;
	protected $_htmlPagerBlock;
	protected $_request;
	protected $_moduleHelper;

	public function __construct(
		\Magento\Framework\UrlInterface $url,
		\Magento\Theme\Block\Html\Pager $htmlPagerBlock,
		\Magento\Framework\App\RequestInterface $request,
		\Blueskytechco\LayeredAjax\Helper\Data $moduleHelper
	) {
		$this->_url = $url;
		$this->_htmlPagerBlock = $htmlPagerBlock;
		$this->_request = $request;
		$this->_moduleHelper = $moduleHelper;
	}

    public function aroundGetUrl(\Magento\Catalog\Model\Layer\Filter\Item $item, $proceed)
    {
		if(!$this->_moduleHelper->isEnabled()){
			return $proceed();
		}

		$value = array();
		$requestVar = $item->getFilter()->getRequestVar();

		if($requestValue = $this->_request->getParam($requestVar)){
			$value = explode(',', $requestValue);
		}
		$value[] = $item->getValue();

		if($requestVar == 'price' && $this->_moduleHelper->isEnabledPriceRangeSliders()){
			$value = ["{price_start}-{price_end}"];
		} else if ($requestVar == 'price' && !$this->_moduleHelper->isEnabledPriceRangeSliders()) {
			return $proceed();
		}

        $query = [
			$item->getFilter()->getRequestVar() => implode(',', $value),
			$this->_htmlPagerBlock->getPageVarName() => null,
        ];
        return $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }

    public function aroundGetRemoveUrl(\Magento\Catalog\Model\Layer\Filter\Item $item, $proceed)
    {
		if(!$this->_moduleHelper->isEnabled()){
			return $proceed();
		}

		$value = array();
		$requestVar = $item->getFilter()->getRequestVar();
		if($requestValue = $this->_request->getParam($requestVar)){
			$value = explode(',', $requestValue);
		}

		if(in_array($item->getValue(), $value)){
			$value = array_diff($value, array($item->getValue()));
		}

		if($requestVar == 'price' && $this->_moduleHelper->isEnabledPriceRangeSliders()){
			$value = [];
		} else if ($requestVar == 'price' && !$this->_moduleHelper->isEnabledPriceRangeSliders()) {
			return $proceed();
		}

        $query = [$requestVar => count($value) ? implode(',', $value) : $item->getFilter()->getResetValue()];
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $query;
        $params['_escape'] = true;
        return $this->_url->getUrl('*/*/*', $params);
    }
}
