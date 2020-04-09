<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Helper\InventoryHelper;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Stocks
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class Stocks implements OptionSourceInterface
{
    /**
     * @var InventoryHelper
     */
    protected $_inventoryHelper;

    /**
     * Stocks constructor.
     *
     * @param InventoryHelper $inventoryHelper
     */
    public function __construct(InventoryHelper $inventoryHelper)
    {
        $this->_inventoryHelper = $inventoryHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->_inventoryHelper->getStockOptions();
    }
}