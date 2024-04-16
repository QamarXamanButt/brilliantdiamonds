<?php
namespace Blueskytechco\LayeredAjax\Cron\Collect;

class Rate
{
    protected $resourceConnection;
    protected $readAdapter;
    protected $writeAdapter;
    protected $storeManager;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    protected $collectionFactory;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->readAdapter = $this->resourceConnection->getConnection('core_read');
        $this->writeAdapter = $this->resourceConnection->getConnection('core_write');
    }
    public function execute()
    {
        $maintable = $this->resourceConnection->getTableName('rating_option_vote_aggregated');
        $tableData = $this->resourceConnection->getTableName('catalog_product_entity_int');
        $tableCatalogProductEntity = $this->resourceConnection->getTableName('catalog_product_entity');
        $eav_attribute = $this->resourceConnection->getTableName('eav_attribute');
        $facets = array(
            1 => '20-39',
            2 => '40-59',
            3 => '60-79',
            4 => '80-99',
            5 => '100',
        );

        $attribute_rate = 'SELECT * FROM '.$eav_attribute.' WHERE attribute_code="rating"';
		$data_attribute = $this->writeAdapter->fetchRow($attribute_rate);
    
        if (isset($data_attribute['attribute_id'])) {
            $attribute_id = $data_attribute['attribute_id'];
            $data = [];
            if (count($facets) > 1) {
                foreach ($facets as $key => $label) {
                    $count = $key;
                    $filter = explode('-', $label);
                    
                    if (count($filter) == 1) {
                        $from = $label;
                        $to = $label;
                    } else {
                        list($from, $to) = $filter;
                    }
                    
                    $query_rate = 'SELECT e.*, rate.percent FROM '.$tableCatalogProductEntity.' AS e LEFT JOIN '.$maintable.' AS rate ON e.entity_id =rate.entity_pk_value WHERE (rate.percent between '.$from.' and '.$to.') GROUP BY e.entity_id';
                    $data_rates = $this->writeAdapter->fetchAll($query_rate);
                    if (count($data_rates)) {
                        foreach($data_rates as $rate){
                            $entity_id = $rate['entity_id'];
                            $sql_config = 'SELECT * FROM '.$tableData.' WHERE attribute_id="'.$attribute_id.'" AND entity_id="'.$entity_id.'"';
                            $data_query = $this->writeAdapter->fetchRow($sql_config);
                            if ($data_query) {
                                $update = "UPDATE " . $tableData . " SET value='".$count."' WHERE attribute_id='".$attribute_id."' AND entity_id='".$entity_id."'";
                                $this->writeAdapter->query($update);
                            } else {
                                $insert = "INSERT INTO " .$tableData . "(attribute_id, entity_id, value) VALUES (".$attribute_id.", ".$entity_id.", ".$count.")";
                                $this->writeAdapter->query($insert);
                            }
                        }
                    }
                }
            }
        }

        return true;
    }
}
