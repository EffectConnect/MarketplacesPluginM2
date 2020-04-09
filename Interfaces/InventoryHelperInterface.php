<?php

namespace EffectConnect\Marketplaces\Interfaces;

/**
 * Interface InventoryHelperInterface
 * @package EffectConnect\Marketplaces\Interfaces
 */
interface InventoryHelperInterface
{
    /**
     * @param string $productSku
     * @param int $websiteId
     * @return float
     */
    public function getProductStockQuantity(string $productSku, int $websiteId) : float;

    /**
     * @param int $entityId
     * @param int $websiteId
     * @return float
     */
    public function getProductStockQuantityById(int $entityId, int $websiteId) : float;

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