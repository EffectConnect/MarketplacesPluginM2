<?php

namespace EffectConnect\Marketplaces\Interfaces;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Interface InventoryHelperInterface
 * @package EffectConnect\Marketplaces\Interfaces
 */
interface InventoryHelperInterface
{
    /**
     * @param ProductInterface $product
     * @param int $websiteId
     * @return float
     */
    public function getProductStockQuantity(ProductInterface $product, int $websiteId) : float;

    /**
     * @return array
     */
    public function getSourceOptions() : array;

    /**
     * @return array
     */
    public function getStockOptions() : array;

    /**
     * @return bool
     */
    public function isTraditionalActive() : bool;

    /**
     * @return bool
     */
    public function isMsiActive() : bool;
}