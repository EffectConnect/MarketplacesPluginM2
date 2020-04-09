<?php

namespace EffectConnect\Marketplaces\Helper;

use EffectConnect\Marketplaces\Interfaces\InventoryHelperInterface;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * This abstract helper class is the base for other inventory helpers.
 *
 * Abstract class BaseInventoryHelper
 * @package EffectConnect\Marketplaces\Helper
 */
abstract class BaseInventoryHelper extends AbstractHelper implements InventoryHelperInterface
{
    /**
     * @param string $productSku
     * @param int $websiteId
     * @return float
     */
    public function getProductStockQuantity(string $productSku, int $websiteId) : float
    {
        return 0;
    }

    /**
     * @param int $entityId
     * @param int $websiteId
     * @return float
     */
    public function getProductStockQuantityById(int $entityId, int $websiteId) : float
    {
        return 0;
    }

    /**
     * @return array
     */
    public function getSourceOptions() : array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getStockOptions() : array
    {
        return [];
    }

    /**
     * @return bool
     */
    public function isTraditionalActive() : bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isMsiActive() : bool
    {
        return
            $this->isMsiAvailable() &&
            $this->isMsiEnabled();
    }

    /**
     * @return bool
     */
    protected function isMsiAvailable() : bool
    {
        return
            interface_exists('Magento\InventoryApi\Api\Data\StockSourceLinkInterface') &&
            interface_exists('Magento\InventoryApi\Api\GetSourceItemsBySkuInterface') &&
            interface_exists('Magento\InventoryApi\Api\GetStockSourceLinksInterface') &&
            interface_exists('Magento\InventoryApi\Api\SourceRepositoryInterface') &&
            interface_exists('Magento\InventoryApi\Api\StockRepositoryInterface') &&
            interface_exists('Magento\InventorySalesApi\Api\GetProductSalableQtyInterface') &&
            interface_exists('Magento\InventorySalesApi\Api\StockResolverInterface') &&
            interface_exists('Magento\InventorySalesApi\Api\Data\SalesChannelInterface');
    }

    /**
     * @return bool
     */
    protected function isMsiEnabled() : bool
    {
        $rootEnabled        = $this->_moduleManager->isEnabled('Magento_Inventory');
        $apiEnabled         = $this->_moduleManager->isEnabled('Magento_InventoryApi');
        $salesApiEnabled    = $this->_moduleManager->isEnabled('Magento_InventorySalesApi');

        return $rootEnabled && $apiEnabled && $salesApiEnabled;
    }
}
