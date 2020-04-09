<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class OrderLine
 * @package EffectConnect\Marketplaces\Model\ResourceModel
 */
class OrderLine extends AbstractDb
{
    public function _construct()
    {
        $this->_init('ec_marketplaces_order_lines', 'entity_id');
    }
}
