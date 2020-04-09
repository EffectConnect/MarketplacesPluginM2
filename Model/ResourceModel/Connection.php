<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Connection
 * @package EffectConnect\Marketplaces\Model\ResourceModel
 */
class Connection extends AbstractDb
{
    public function _construct()
    {
        $this->_init('ec_marketplaces_connection', 'entity_id');
    }
}
