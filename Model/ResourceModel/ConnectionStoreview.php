<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ConnectionStoreview
 * @package EffectConnect\Marketplaces\Model\ResourceModel
 */
class ConnectionStoreview extends AbstractDb
{
    public function _construct()
    {
        $this->_init('ec_marketplaces_connection_storeview', 'entity_id');
    }
}
