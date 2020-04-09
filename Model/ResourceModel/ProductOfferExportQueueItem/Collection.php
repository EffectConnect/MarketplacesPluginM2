<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel\ProductOfferExportQueueItem;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use EffectConnect\Marketplaces\Model\ProductOfferExportQueueItem as ProductOfferExportQueueItemModel;
use EffectConnect\Marketplaces\Model\ResourceModel\ProductOfferExportQueueItem as ProductOfferExportQueueItemResourceModel;

/**
 * Class Collection
 * @package EffectConnect\Marketplaces\Model\ResourceModel\ProductOfferExportQueueItem
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
        $this->_init(ProductOfferExportQueueItemModel::class, ProductOfferExportQueueItemResourceModel::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
