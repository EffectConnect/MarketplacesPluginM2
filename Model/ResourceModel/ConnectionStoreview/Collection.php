<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel\ConnectionStoreview;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use EffectConnect\Marketplaces\Model\ConnectionStoreview as ConnectionStoreviewModel;
use EffectConnect\Marketplaces\Model\ResourceModel\ConnectionStoreview as ConnectionStoreviewResourceModel;

/**
 * Class Collection
 * @package EffectConnect\Marketplaces\Model\ResourceModel\ConnectionStoreview
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(ConnectionStoreviewModel::class, ConnectionStoreviewResourceModel::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
