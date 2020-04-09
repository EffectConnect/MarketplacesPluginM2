<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Log
 * @package EffectConnect\Marketplaces\Model\ResourceModel
 */
class Log extends AbstractDb
{
    public function _construct()
    {
        $this->_init('ec_marketplaces_log', 'entity_id');
    }
}
