<?php

namespace EffectConnect\Marketplaces\Model;

use Magento\Framework\Model\AbstractModel;
use EffectConnect\Marketplaces\Model\ResourceModel\DirectCatalogExportQueueItem as DirectCatalogExportQueueItemResourceModel;

/**
 * Class DirectCatalogExportQueueItem
 * @method string|null getConnectionEntityId()
 * @method string|null getCreatedAt()
 * @method string|null getExecutedAt()
 * @method DirectCatalogExportQueueItem setConnectionEntityId(int $productId)
 * @method DirectCatalogExportQueueItem setExecutedAt(int|null $timestamp)
 * @package EffectConnect\Marketplaces\Model
 */
class DirectCatalogExportQueueItem extends AbstractModel
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(DirectCatalogExportQueueItemResourceModel::class);
    }
}
