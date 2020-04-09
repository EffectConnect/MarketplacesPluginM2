<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Channel
 * @package EffectConnect\Marketplaces\Model\ResourceModel
 */
class Channel extends AbstractDb
{
    public function _construct()
    {
        $this->_init('ec_marketplaces_channel', 'entity_id');
    }
}
