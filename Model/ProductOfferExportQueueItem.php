<?php

namespace EffectConnect\Marketplaces\Model;

use Magento\Framework\Model\AbstractModel;
use EffectConnect\Marketplaces\Model\ResourceModel\ProductOfferExportQueueItem as ProductOfferExportQueueItemResourceModel;

/**
 * Class ProductOfferExportQueueItem
 * @method string|null getCatalogProductEntityId()
 * @method string|null getCreatedAt()
 * @method string|null getExecutedAt()
 * @method ProductOfferExportQueueItem setCatalogProductEntityId(int $productId)
 * @method ProductOfferExportQueueItem setExecutedAt(int|null $timestamp)
 * @package EffectConnect\Marketplaces\Model
 */
class ProductOfferExportQueueItem extends AbstractModel
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(ProductOfferExportQueueItemResourceModel::class);
    }
}
