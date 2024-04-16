<?php
/**
 * Copyright © 2019 Blueskytechco. All rights reserved.
 */

namespace Blueskytechco\StoreLocator\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    /**
     * @return string|null
     */
    public function getGoogleApiKeyFrontend()
    {
        return $this->scopeConfig->getValue('storelocator/google_api_key/frontend', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }

    public function getTimeFromTo($time_today)
    {
        $format_time = $this->scopeConfig->getValue('catalog/custom_options/time_format', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $html = '';
        $time_am = '';
        $time_pm = '';
        if($time_today->from->hours < 10){
            $time_am .= '0';
        }
        $time_am .= $time_today->from->hours.':';
        if($time_today->from->minutes < 10){
            $time_am .= '0';
        }
        $time_am .= $time_today->from->minutes.' AM';
        
        if($time_today->to->hours < 10){
            $time_pm .= '0';
        }
        $time_pm .= $time_today->to->hours.':';
        if($time_today->to->minutes < 10){
            $time_pm .= '0';
        }
        $time_pm .= $time_today->to->minutes.' PM';
        if($format_time == '12h'){
            $html = $time_am.' - '.$time_pm;
        }
        else{
            $html = date("H:i", strtotime($time_am)).' - '.date("H:i", strtotime($time_pm));
        }
        return $html;
    }
}
