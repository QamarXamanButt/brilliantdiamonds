<?php
namespace Blueskytechco\OptimizeSpeed\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;

class OptimizeSpeedHttp implements ObserverInterface {

	protected $_scopeConfig;
	protected $_appState;
	protected $_remoteAddress;
	protected $_httpHeader;
	protected $_getFile;
	protected $_geDir;
	protected $_parser;

	public function __construct(
		\Magento\Framework\Filesystem $file,
    	\Magento\Framework\App\State $appState,
    	\Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
    	\Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    	$this->_getFile = $file;
    	$this->_httpHeader = $httpHeader;
    	$this->_appState = $appState;
        $this->_scopeConfig = $scopeConfig;
        $this->_remoteAddress = $remoteAddress;
        $this->_geDir = $this->_getFile->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/Blueskytechco/OptimizeSpeed');
        $this->_parser = new \Magento\Framework\Xml\Parser();
    }
	
	public function execute(Observer $observer) {
		$response = $observer->getResponse();
		$this->customResponse($response);
    }

    public function customResponse($response) {
    	if (strpos($response->getBody(), '</head>') !== false) {
    		$content = $response->getBody();

	        if($this->getConfiguration('themeoption/general/layout') == '' || $this->getConfiguration('themeoption/general/layout') != '100%'){
	        	$content = preg_replace('# page-layout-product-full-width#', '', $content);
	        }

	        if (preg_match_all('/<div([^>]*?)data-background-images=(\"|\'|)(.*?)(\"|\'|)(.*?)>/is', $content, $data_images)) {
	            $data_images_count = count($data_images);
				foreach ($data_images[0] as $key => $image) {
	                $attributes = '';
	                foreach ($data_images as $key_attribute => $attribute) {
	                    if ($key_attribute < 1) {
	                        continue;
	                    }
	                    if(strpos($data_images[$key_attribute][$key], 'class') !== false){
	                        $attributes = $data_images[$key_attribute][$key];
	                        break;
	                    }
	                }
					if (preg_match_all('/class="([^>]*?)(.*?)"/is', $attributes, $data_class)) {
						if(isset($data_class[2])){
							if(isset($data_class[2][0]) && $data_class[2][0]){
								$class = explode(" ",$data_class[2][0]);
								foreach ($class as $item) {
									if($item){
										if(preg_match_all('#<style type="text/css">(.*?)</style>#is', $content, $data_style)) {
											if(isset($data_style[0]) && isset($data_style[1])){
												foreach($data_style[0] as $key_style => $style){
													if(isset($data_style[1][$key_style])){
														$attributes_style = $data_style[1][$key_style];
														if($attributes_style){
															if(strpos($attributes_style, $item) !== false){
																$data_bgset = '';
																if (preg_match_all("/data-background-images='([^>]*?)(.*?)'/is", $data_images[0][$key], $images)){
																	if(isset($images[2][0])){
																		$bgset_images = str_replace('\\','',$images[2][0]);
																		$bgset_images = json_decode($bgset_images,true);
																		if($bgset_images && count($bgset_images) > 0){
																			if(isset($bgset_images['desktop_image'])){
																				$data_bgset = $bgset_images['desktop_image'];
																				if(isset($bgset_images['mobile_image'])){
																					$data_bgset = $bgset_images['mobile_image']. ' [(max-width: 768px)] | '.$data_bgset;
																				} 
																			}else{
																				if(isset($bgset_images['mobile_image'])){
																					$data_bgset = $bgset_images['mobile_image']. ' [(max-width: 768px)]';
																				}
																			}
																		}
																	}
																}
																if($data_bgset){
																	if ($image_webp_enabled) {
																		$string = strrchr($src, ".");
																		if($ignore_list){
																			foreach($ignore_list as $value){
																				if($value && strpos($src, $value) !== false){
																					$data_bgset = str_replace($string, '.webp', $data_bgset);
																					continue;
																				}
																			}
																		}
																	}
																	$data_bgset = 'data-bgset="'.$data_bgset.'"';
																	$content = str_replace($style,'', $content);
																	$new_content = $data_class[2][0].' lazyload" '.$data_bgset;
																	$content = str_replace($data_class[2][0].'"',$new_content, $content);
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
			
			$content = preg_replace('#<style type="text/css">#', '<style>', $content);
			
			if(preg_match_all('#<style>(.*?)</style>#is', $content, $inline_styles)) {
				if(isset($inline_styles[0])){
					foreach($inline_styles[0] as $key_inline_styles => $inline_style){
						if(strpos($inline_style, '#html-body') !== false || strpos($inline_style, 'rs-') !== false || strpos($inline_style, 'ui-menu-item') !== false){
							$content = str_replace($inline_style,'', $content);
							$content = str_replace('</head>',$inline_style.'</head>', $content);
						}
					}
				}
			}

	        $response->setBody($content);
        }
    }

    public function getConfiguration($path, $storeId = null)
	{
		return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
	}
}
