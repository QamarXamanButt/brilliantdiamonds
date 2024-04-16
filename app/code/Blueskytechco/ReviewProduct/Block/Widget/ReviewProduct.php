<?php
/**
 * Copyright © 2023 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Blueskytechco\ReviewProduct\Block\Widget;

use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Filesystem\DirectoryList;

class ReviewProduct extends \Magento\Catalog\Block\Product\AbstractProduct  implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'Blueskytechco_ReviewProduct::widget/carousel.phtml';
    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $_productRepositoryFactory;

    /**
     * constructor.
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getAllReviews()
    {
        $number_products = $this->getDataWidgetConfig('number_reviews') ? $this->getDataWidgetConfig('number_reviews') : 5;
        $type = $this->getDataWidgetConfig('type_review') ? $this->getDataWidgetConfig('type_review') : '';
        if ($type == 'product') {
            $reviewsCollection = $this->reviewCollectionFactory->create();
            $reviewsCollection->getSelect()->group('entity_pk_value')->columns("max(`main_table`.`review_id`) as review_id_pk");
            $reviewsCollection->getSelect()->order('review_id_pk desc');
            $reviewsCollection->addStoreFilter($this->storeManager->getStore()->getStoreId())
                ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
                ->setPageSize($number_products)
                ->setCurPage(1);
            $_collection = $reviewsCollection->getItems();
            $array_id = [];
            if (count($_collection)) {
                foreach ($_collection as $_review) {
                    $array_id[] = $_review['review_id_pk'];
                }
            }
            $reviewsCollection_new = $this->reviewCollectionFactory->create();
            $reviewsCollection_new->getSelect()->order('created_at desc');
            $reviewsCollection_new->addFieldToFilter('main_table.review_id',array('in' => $array_id))
                ->addRateVotes();
            return $reviewsCollection_new;
        } else {
            $reviewsCollection = $this->reviewCollectionFactory->create();
            $reviewsCollection->getSelect()->order('created_at desc');
            $reviewsCollection->addStoreFilter($this->storeManager->getStore()->getStoreId())
                ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
                ->setPageSize($number_products)
                ->setCurPage(1)
                ->addRateVotes();
            return $reviewsCollection;
        }
    }

    public function getDataWidgetConfig($path)
    {
        return $this->getData($path) ?: '';
    }

    public function getProductById($idProduct)
    {
        $product = $this->_productRepositoryFactory->create()->getById($idProduct);
        return $product;
    }
}