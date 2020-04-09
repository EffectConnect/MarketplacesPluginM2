<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Enums\YesNo;
use EffectConnect\Marketplaces\Helper\InventoryHelper;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class MsiActive
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class MsiActive implements OptionSourceInterface
{
    /**
     * @var InventoryHelper
     */
    protected $_inventoryHelper;

    /**
     * MsiActive constructor.
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
        $enumValue              = YesNo::YES();

        if (!$this->_inventoryHelper->isMsiActive()) {
            $enumValue          = YesNo::NO();
        }

        return [
            [
                'value' => $enumValue->getValue(),
                'label' => $enumValue->getLabel()
            ]
        ];
    }
}