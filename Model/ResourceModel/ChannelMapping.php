<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ChannelMapping
 * @package EffectConnect\Marketplaces\Model\ResourceModel
 */
class ChannelMapping extends AbstractDb
{
    public function _construct()
    {
        $this->_init('ec_marketplaces_channel_mapping', 'entity_id');
    }
}
