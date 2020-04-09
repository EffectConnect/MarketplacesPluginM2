<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel\ChannelMapping;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use EffectConnect\Marketplaces\Model\ChannelMapping as ChannelMappingModel;
use EffectConnect\Marketplaces\Model\ResourceModel\ChannelMapping as ChannelMappingResourceModel;

/**
 * Class Collection
 * @package EffectConnect\Marketplaces\Model\ResourceModel\ChannelMapping
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(ChannelMappingModel::class, ChannelMappingResourceModel::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @return $this|AbstractCollection|void
     */
    public function _initSelect()
    {
        parent::_initSelect();

        // Join channel mapping with channel database table to be able to fetch the EffectConnect channel ID.
        $this->getSelect()->joinLeft(
            ['channel_table' => $this->getTable('ec_marketplaces_channel')],
            'channel_table.entity_id = main_table.channel_id',
            ['ec_channel_id' => 'ec_channel_id']
        );

        // Make sure the 'connection_id' and 'entity_id' are unique.
        $this->addFilterToMap('connection_id', 'main_table.connection_id');
        $this->addFilterToMap('entity_id', 'main_table.entity_id');

        return $this;
    }
}
