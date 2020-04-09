<?php

namespace EffectConnect\Marketplaces\Model;

use Magento\Framework\Model\AbstractModel;
use EffectConnect\Marketplaces\Model\ResourceModel\LogExportQueueItem as LogExportQueueItemResourceModel;

/**
 * Class LogExportQueueItem
 * @method string|null getConnectionEntityId()
 * @method string|null getCreatedAt()
 * @method string|null getExecutedAt()
 * @method LogExportQueueItem setConnectionEntityId(int $productId)
 * @method LogExportQueueItem setExecutedAt(int|null $timestamp)
 * @package EffectConnect\Marketplaces\Model
 */
class LogExportQueueItem extends AbstractModel
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(LogExportQueueItemResourceModel::class);
    }
}
