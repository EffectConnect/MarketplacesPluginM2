<?php

namespace EffectConnect\Marketplaces\Model\ResourceModel\Log;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use EffectConnect\Marketplaces\Model\Log as LogModel;
use EffectConnect\Marketplaces\Model\ResourceModel\Log as LogResourceModel;

/**
 * Class Collection
 * @package EffectConnect\Marketplaces\Model\ResourceModel\Log
 */
class Collection extends AbstractCollection
{
    /**
     * Initialization here
     *
     * @return void
     * @throws LocalizedException
     */
    public function _construct()
    {
        $this->_init(LogModel::class, LogResourceModel::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
