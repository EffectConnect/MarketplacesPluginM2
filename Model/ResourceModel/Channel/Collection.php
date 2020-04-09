<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel\Channel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use EffectConnect\Marketplaces\Model\Channel as ChannelModel;
use EffectConnect\Marketplaces\Model\ResourceModel\Channel as ChannelResourceModel;

/**
 * Class Collection
 * @package EffectConnect\Marketplaces\Model\ResourceModel\Channel
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(ChannelModel::class, ChannelResourceModel::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
