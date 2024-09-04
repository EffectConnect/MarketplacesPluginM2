<?php

namespace EffectConnect\Marketplaces\Helper;

use EffectConnect\Marketplaces\Exception\UnsupportedBundleException;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Type\AbstractType;

/**
 * Class BundleHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class BundleHelper
{
    /**
     * Currently only bundle products with exactly one option for each bundle item are supported.
     *
     * @param ProductModel $product
     * @return array
     * @throws UnsupportedBundleException
     */
    public function getBundleOptions(ProductModel $product): array
    {
        if (intval($product->getShipmentType()) !== AbstractType::SHIPMENT_TOGETHER) {
            throw new UnsupportedBundleException(__('Only bundles with setting \'Ship Bundle Items\' set to \'Together\' are supported.'));
        }
        $selectionCollection = $product->getTypeInstance()
            ->getSelectionsCollection(
                $product->getTypeInstance()->getOptionsIds($product),
                $product
            );
        $bundleOptions = [];
        foreach ($selectionCollection as $selection) {
            $bundleOptions[$selection->getOptionId()][] = $selection->getSelectionId();
            if (count($bundleOptions[$selection->getOptionId()]) > 1) {
                throw new UnsupportedBundleException(__('Bundles with multiple options are not supported.'));
            }
        }
        return $bundleOptions;
    }
}
