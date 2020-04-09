<?php

namespace EffectConnect\Marketplaces\Helper;

use Magento\Framework\App\ObjectManager;

/**
 * Class MultiSourceInventoryChecker
 * @package EffectConnect\Marketplaces\Helper
 */
class MultiSourceInventoryChecker
{
    /**
     * @return bool
     */
    public static function msiEnabled(): bool
    {
        $objectManager   = ObjectManager::getInstance();
        $moduleManager   = $objectManager->create('Magento\Framework\Module\Manager');
        $rootEnabled     = $moduleManager->isEnabled('Magento_Inventory');
        $apiEnabled      = $moduleManager->isEnabled('Magento_InventoryApi');
        $salesApiEnabled = $moduleManager->isEnabled('Magento_InventorySalesApi');
        return $rootEnabled && $apiEnabled && $salesApiEnabled;
    }

    /**
     * @return bool
     */
    public static function traditionalEnabled(): bool
    {
        $objectManager   = ObjectManager::getInstance();
        $moduleManager   = $objectManager->create('Magento\Framework\Module\Manager');
        $enabled         = $moduleManager->isEnabled('Magento_CatalogInventory');
        return $enabled;
    }
}