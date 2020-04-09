<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class DirectCatalogExportQueueItem
 * @package EffectConnect\Marketplaces\Model\ResourceModel
 */
class DirectCatalogExportQueueItem extends AbstractDb
{
    public function _construct()
    {
        $this->_init('ec_marketplaces_direct_catalog_export_queue', 'entity_id');
    }
}
