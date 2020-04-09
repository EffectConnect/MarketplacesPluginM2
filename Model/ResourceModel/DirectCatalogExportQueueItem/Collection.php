<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel\DirectCatalogExportQueueItem;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use EffectConnect\Marketplaces\Model\DirectCatalogExportQueueItem as DirectCatalogExportQueueItemModel;
use EffectConnect\Marketplaces\Model\ResourceModel\DirectCatalogExportQueueItem as DirectCatalogExportQueueItemResourceModel;

/**
 * Class Collection
 * @package EffectConnect\Marketplaces\Model\ResourceModel\DirectCatalogExportQueueItem
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
        $this->_init(DirectCatalogExportQueueItemModel::class, DirectCatalogExportQueueItemResourceModel::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
