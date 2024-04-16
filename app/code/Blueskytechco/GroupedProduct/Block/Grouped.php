<?php
namespace Blueskytechco\GroupedProduct\Block;

use \Magento\Catalog\Block\Product\AbstractProduct;
use \Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Grouped extends AbstractProduct implements BlockInterface
{
    protected $_template = 'index.phtml';
    protected $_productCollectionFactory;
    private $urlEncoder;
    private $priceCurrency;
    protected $_productloader;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        EncoderInterface $urlEncoder = null,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->urlEncoder = $urlEncoder ?: ObjectManager::getInstance()->get(EncoderInterface::class);
        $this->_productloader = $_productloader;
        $this->priceCurrency = $priceCurrency;
		parent::__construct($context, $data);
    }

    public function _getProduct() {
		$id = explode(",",$this->getData('product_id'));
		$collection = $this->_productCollectionFactory->create();
		$collection->addAttributeToSelect("*");
		$collection->addAttributeToFilter('entity_id', ['in' => $id]);
		$collection->addAttributeToFilter("type_id", ['in' => 'grouped']);
		return $collection;
    }

    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($url),
            ]
        ];
    }

    public function getProductItem($product_id)
    {
        return $this->_productloader->create()->load($product_id);
    }

    public function getFormatedPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat($amount);
    }

    public function getCanShowProductPrice($product)
    {
        return true;
    }

    public function getAssociatedProducts($product)
    {
        $result = $product->getTypeInstance()->getAssociatedProducts($product);
        $storeId = $product->getStoreId();
        foreach ($result as $item) {
            $item->setStoreId($storeId);
        }
        return $result;
    }
}
