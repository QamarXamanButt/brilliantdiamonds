<?php

namespace Criteo\OneTag\Helper;

/**
 * Description of TagGenerator
 *
 * @author Criteo
 */
class TagGenerator extends \Magento\Framework\App\Helper\AbstractHelper {

    const SETTINGS_PARTNER_ID = 'cto_onetag_section/general/cto_partner';
    const SETTINGS_VERSION = 'cto_onetag_section/general/cto_version';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger = $logger;
        parent::__construct($context);
    }
    
    private $_params = array();
    private $_dataLayer = array();
    private $_allow_methods = array(
        'setAccount',
        'setEmail',
        'setHashedEmail',
        'setSiteType',
        'trackTransaction',
        'viewHome',
        'viewList',
        'viewItem',
        'viewBasket'
    );

    public function __call($method, $args) {
        if (in_array($method, $this->_allow_methods)) {
            return $this->add_param($method, $args);
        }
    }

    public function cto_get_code() {

        $version = $this->scopeConfig->getValue(self::SETTINGS_VERSION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $partnerId = $this->scopeConfig->getValue(self::SETTINGS_PARTNER_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $ecp_plugin = 'magento2-' . $version;
        $tag = implode(",\n", $this->_params);

        $code = <<<EOT
        <!-- CRITEO ONETAG MAGENTO 2 EXTENSION VERSION $version -->
            <!-- START OF CRITEO ONETAG -->
            <script type="text/javascript" src="//dynamic.criteo.com/js/ld/ld.js?a=$partnerId" async="true"></script>
            <script type="text/javascript">
                var deviceType = (window.innerWidth <= 767) ? 'm' : (window.innerWidth >= 980) ? 'd' : 't';
                window.criteo_q = window.criteo_q || [];
                window.criteo_q.push({"event": "setSiteType", "type": deviceType, "ecpplugin": "$ecp_plugin"});
                window.criteo_q.push( {$tag} );
            </script>
            <!-- END OF CRITEO ONETAG -->
EOT;

        return $code;
    }

    public function cto_set_dataLayer() {

        //map data layer info to data layer defined spec
        $output = array();
        $event = '';

        foreach ($this->_dataLayer as $row) {
            switch ($row['event']) {
                case 'setEmail':
                    $output['email'] = $row['email'];
                    break;
                case 'viewList':
                    $event = 'listingpage';
                    $output['impressions'] = $row['item'];
                    break;
                case 'viewItem':
                    $event = 'productpage';
                    $product = array('id' => $row['item']);
                    $output['detail'] = array('products' => array($product));
                    break;
                case 'viewBasket':
                    $event = 'checkout';
                    $products = $row['item'];
                    $output['checkout'] = array('products' => $products);
                    break;
                case 'trackTransaction':
                    $event = 'transactionpage';
                    $output['purchase'] = array(
                        'actionField' => array(
                            'id' => $row['id'],
                            'zipcode' => $row['zipcode'],
                            'currency' => $row['currency']
                        ),
                        'products' => $row['item']
                    );
                    break;
                default:
                    break;
            }
        }

        $dataLayer = json_encode($output);

        $code = <<<EOT
            <script type="text/javascript">
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    'event': '$event',
                    'ecommerce': 
                        $dataLayer
                });
            </script>
EOT;
        return $code;
    }

    private function add_param($event, $array) {
        $param = array('event' => $event);

        if (count($array) > 0) {
            foreach ($array[0] as $key => $value) {
                $param[$key] = $value;
            }
        }

        $this->_params[] = json_encode($param);
        $this->_dataLayer[] = $param;
    }

}
