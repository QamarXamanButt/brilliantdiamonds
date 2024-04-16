<?php
declare(strict_types=1);

namespace Blueskytechco\CustomCatalog\Model\Product;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as BundleType;

class SingleChoiceProvider extends \Magento\Bundle\Model\Product\SingleChoiceProvider
{
	public function isSingleChoiceAvailable(Product $product): bool
    {
        $result = false;
        if ($product->getTypeId() !== BundleType::TYPE_BUNDLE) {
            return $result;
        }
        
        if (!$this->hasRequiredOptions($product)) {
            return $result;
        }

        return $this->hasCustomizations($product);
    }

    private function hasRequiredOptions(Product $product): bool
    {
        $result = true;
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);

        if (!$typeInstance->hasRequiredOptions($product)) {
            $result = false;
        }

        return $result;
    }

    private function hasCustomizations(Product $product): bool
    {
        $typeInstance = $product->getTypeInstance();
        $isNoCustomizations = true;
        foreach ($typeInstance->getOptions($product) as $option) {
            if ($isNoCustomizations && (int)$option->getRequired() === 1) {
                $selectionsCollection = $typeInstance->getSelectionsCollection(
                    [$option->getId()],
                    $product
                );

                if ($selectionsCollection->count() > 1) {
                    foreach ($selectionsCollection as $selection) {
                        if ($isNoCustomizations) {
                            $isNoCustomizations = (int)$selection->getData('is_default') === 1
                                && (int)$selection->getData('selection_can_change_qty') === 0;
                        } else {
                            break;
                        }
                    }
                }
            } else {
                $isNoCustomizations = false;
                break;
            }
        }

        return $isNoCustomizations;
    }
}