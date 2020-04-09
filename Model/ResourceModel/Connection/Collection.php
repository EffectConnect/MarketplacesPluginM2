<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel\Connection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use EffectConnect\Marketplaces\Model\Connection as ConnectionModel;
use EffectConnect\Marketplaces\Model\ResourceModel\Connection as ConnectionResourceModel;

/**
 * Class Collection
 * @package EffectConnect\Marketplaces\Model\ResourceModel\Connection
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(ConnectionModel::class, ConnectionResourceModel::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
