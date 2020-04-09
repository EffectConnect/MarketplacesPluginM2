<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class LogExportQueueItem
 * @package EffectConnect\Marketplaces\Model\ResourceModel
 */
class LogExportQueueItem extends AbstractDb
{
    public function _construct()
    {
        $this->_init('ec_marketplaces_log_export_queue', 'entity_id');
    }
}
