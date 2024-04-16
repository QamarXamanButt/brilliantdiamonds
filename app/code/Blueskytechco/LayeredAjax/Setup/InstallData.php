<?php
namespace Blueskytechco\LayeredAjax\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
	const PRODUCT_GROUP = 'Product Details';
    protected $state;
    protected $attributeRepository;
    protected $filesystem;
    protected $swatchHelper;
    protected $productMediaConfig;
    protected $driverFile;
    protected $eavSetupFactory;

	public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\App\State $state,
        \Magento\Catalog\Model\Product\Attribute\Repository $attributeRepository,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Swatches\Helper\Media $swatchHelper,
        \Magento\Catalog\Model\Product\Media\Config $productMediaConfig,
        \Magento\Framework\Filesystem\Driver\File $driverFile
    ){
        $this->eavSetupFactory = $eavSetupFactory;
        $this->state = $state;
        $this->attributeRepository = $attributeRepository;
        $this->filesystem = $filesystem;
        $this->swatchHelper = $swatchHelper;
        $this->productMediaConfig = $productMediaConfig;
        $this->driverFile = $driverFile;
    }

	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
        try{
            $this->state->getAreaCode();
        }
        catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $this->state->setAreaCode('adminhtml');
        }
		$eavSetup = $this->eavSetupFactory->create();
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'rating',
            [
                'type' => 'int',
                'label' => 'Rating Product',
                'input' => 'select',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'source' => 'Blueskytechco\LayeredAjax\Model\Rating\Values',
                'required' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => self::PRODUCT_GROUP,
                'used_in_product_listing' => true,
                'visible_on_front' => false,
                'user_defined' => false,
                'filterable' => 2,
                'filterable_in_search' => true,
                'used_for_promo_rules' => false,
                'is_html_allowed_on_front' => false,
                'used_for_sort_by' => false,
            ],
        );
	}
}