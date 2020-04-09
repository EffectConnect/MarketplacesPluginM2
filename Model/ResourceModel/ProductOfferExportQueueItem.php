<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ProductOfferExportQueueItem
 * @package EffectConnect\Marketplaces\Model\ResourceModel
 */
class ProductOfferExportQueueItem extends AbstractDb
{
    public function _construct()
    {
        $this->_init('ec_marketplaces_product_offer_export_queue', 'entity_id');
    }
}
