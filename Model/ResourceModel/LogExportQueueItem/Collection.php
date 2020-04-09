<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel\LogExportQueueItem;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use EffectConnect\Marketplaces\Model\LogExportQueueItem as LogExportQueueItemModel;
use EffectConnect\Marketplaces\Model\ResourceModel\LogExportQueueItem as LogExportQueueItemResourceModel;

/**
 * Class Collection
 * @package EffectConnect\Marketplaces\Model\ResourceModel\LogExportQueueItem
 */
class Collection extends AbstractCollection
{
    /**
     * Initialization here
     *
     * @return void
     * @throws LocalizedException
     */
    public function _construct()
    {
        $this->_init(LogExportQueueItemModel::class, LogExportQueueItemResourceModel::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
