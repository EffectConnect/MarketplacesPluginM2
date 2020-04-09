<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Helper\InventoryHelper;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Sources
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class Sources implements OptionSourceInterface
{
    /**
     * @var InventoryHelper
     */
    protected $_inventoryHelper;

    /**
     * Sources constructor.
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
    public function toOptionArray($type = false)
    {
        return $this->_inventoryHelper->getSourceOptions();
    }
}