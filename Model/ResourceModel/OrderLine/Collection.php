<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel\OrderLine;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use EffectConnect\Marketplaces\Model\OrderLine as OrderLineModel;
use EffectConnect\Marketplaces\Model\ResourceModel\OrderLine as OrderLineResourceModel;

/**
 * Class Collection
 * @package EffectConnect\Marketplaces\Model\ResourceModel\OrderLine
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(OrderLineModel::class, OrderLineResourceModel::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
