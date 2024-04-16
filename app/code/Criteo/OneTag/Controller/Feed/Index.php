<?php

namespace Criteo\OneTag\Controller\Feed;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\Request\Http;
use \Magento\Catalog\Model\ProductRepository;
use \Magento\CatalogInventory\Model\Stock\StockItemRepository;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\ResourceConnection;
use \Magento\Catalog\Helper\Category;
use \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use \Magento\Eav\Model\ResourceModel\Entity\Attribute;
use \Magento\Framework\App\ProductMetadataInterface;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $_request;
    protected $_productRepository;
    protected $_objectManager;
    protected $_stockRepository;
    protected $_scopeConfig;
    protected $_dbConnection;
    protected $_productTypeConfigurable;
    protected $_eavAttribute;
    protected $_productMetadata;

    public function __construct(
        Context $context,
        Http $request,
        ProductRepository $productRepository,
        StockItemRepository $stockRepository,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $connection,
        Category $categoryHelper,
        Configurable $productTypeConfigurable,
        Attribute $eavAttribute,
        ProductMetadataInterface $productMetadata
    ) {
        $this->_request = $request;
        $this->_productRepository = $productRepository;
        $this->_stockRepository = $stockRepository;
        $this->_scopeConfig = $scopeConfig;
        $this->_dbConnection = $connection;
        $this->_categoryHelper = $categoryHelper;
        $this->_productTypeConfigurable = $productTypeConfigurable;
        $this->_eavAttribute = $eavAttribute;
        $this->_productMetadata = $productMetadata;

        $this->_objectManager = $context->getObjectManager();

        parent::__construct($context);
    }

    public function execute()
    {

        //get parameters
        $page = (int) $this->_request->getParam('page');
        $limit = (int) $this->_request->getParam('limit');

        //default params values
        $page = max(1, $page);
        $limit = max(50, $limit); //lower bound
        $limit = min($limit, 250); //upper bound

        //get product ids with pagination
        $productItems = $this->getProductIds($page, $limit);
        $feed_data = array();

        //do caching (if any)
        $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $baseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
        $categoryNames = $this->getCategoryNames();

        foreach ($productItems as $item) {
            $product = new \stdClass();

            //ids
            $product->id = $item['entity_id'];
            $product->sku = $item['sku'];

            $obj = $this->_productRepository->getById($product->id);

            //has parent?
            $product->item_group_id = '';
            if ($item['type_id'] == 'simple') {
                $parent = $this->_productTypeConfigurable->getParentIdsByChild($product->id);
                if (isset($parent[0])) {
                    $product->item_group_id = $parent[0];
                }
            }

            //name
            $product->title = $obj->getName();

            //description
            $product->description = $obj->getShortDescription();

            //product url
            $product->link = $obj->getProductUrl();

            //image link
            $product->image_link = $baseUrl . $obj->getImage();

            //retail Price
            $product->price = $obj->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)->getValue();

            //sale price
            $product->sale_price = $obj->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)->getValue();

            //category info
            $categoryIds = $obj->getCategoryIds();
            $tmpCategoryNames = array();
            foreach ($categoryIds as $id) {
                if (isset($categoryNames[$id])) {
                    array_push($tmpCategoryNames, $categoryNames[$id]);
                }
            }
            $product->product_type = implode(' > ', $tmpCategoryNames);

            //availability
            try {
                $stock = $this->_stockRepository->get($product->id);
                if (isset($stock)) {
                    $product->availability = $stock->getIsInStock() == true ? 'in stock' : 'out of stock';
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exc) {
                //no stock management, set to in stock
                $product->availability = 'in stock';
            }

            array_push($feed_data, $product);
        }

        $xml_feed = self::generate_xml_feed($feed_data);

        //output XML to browser
        header('Content-type: text/xml; charset=utf-8');
        echo($xml_feed);
    }

    private function generate_xml_feed($feed_data)
    {
        $xmlWriter = new \XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);
        $xmlWriter->setIndentString("    ");
        $xmlWriter->startDocument('1.0', 'UTF-8');
        $xmlWriter->startElement('rss');
        $xmlWriter->writeAttribute('version', '2.0');
        $xmlWriter->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $xmlWriter->startElement('channel');
        foreach ($feed_data as $row) {
            $xmlWriter->startElement('item');
            foreach ($row as $key => $value) {
                $xmlWriter->startElement('g:'.$key);
                $feedvalue = is_null($value) ? "" : $value;
                in_array($key, ['title', 'description']) ? $xmlWriter->writeCData($feedvalue) : $xmlWriter->text($feedvalue);
                $xmlWriter->endElement(); //g:KEY
            }
            $xmlWriter->endElement(); //item
        }
        $xmlWriter->endElement(); //channel
        $xmlWriter->endElement(); //rss
        return $xmlWriter->flush(true);
    }

    /*
     * Return array of product ids
     */
    private function getProductIds($page, $limit)
    {
        $ATTRIBUTE_ID_STATUS = $this->_eavAttribute->getIdByCode('catalog_product', 'status');
        $ATTRIBUTE_ID_VISIBILITY = $this->_eavAttribute->getIdByCode('catalog_product', 'visibility');
        $STATUS_ENABLED = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        $VISIBILITY_NOT_VISIBLE = \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE;

        $start = ($page - 1) * $limit;
        $connection = $this->_dbConnection->getConnection();
        $tableCpe = $connection->getTableName('catalog_product_entity');
        $tableCpei = $connection->getTableName('catalog_product_entity_int');

        $edition = strtolower($this->_productMetadata->getEdition());
        $linkColumn = 'entity_id';

        // support Magento EE changes in column
        // https://magento.stackexchange.com/questions/139740/magento-2-schema-changes-for-ee-catalog-staging
        if ($edition == 'enterprise') {
            $linkColumn = 'row_id';
        }

        $query = <<<EOT
		SELECT a.entity_id,
		       a.type_id,
		       a.sku
		FROM   $tableCpe AS a
		       LEFT JOIN $tableCpei AS b1 ON a.entity_id = b1.$linkColumn AND b1.attribute_id = $ATTRIBUTE_ID_STATUS
		       LEFT JOIN $tableCpei AS b2 ON a.entity_id = b2.$linkColumn AND b2.attribute_id = $ATTRIBUTE_ID_VISIBILITY
		WHERE b1.value = $STATUS_ENABLED
	    AND b2.value != $VISIBILITY_NOT_VISIBLE
	    LIMIT $start, $limit
EOT;
        $result = $connection->fetchAll($query);
        return $result;
    }

    /*
     * Get set of categories name
     */
    private function getCategoryNames()
    {
        $sorted = false;
        $asCollection = true;
        $toLoad = true;
        $names = array();
        $categories = $this->_categoryHelper->getStoreCategories($sorted, $asCollection, $toLoad);

        foreach ($categories as $category) {
            $names[$category->getId()] = $category->getName();
        }
        return $names;
    }
}
