<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use EffectConnect\Marketplaces\Enums\ShipmentEvent as ShipmentEventEnum;

/**
 * Class ShipmentEvent
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class ShipmentEvent implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return ShipmentEventEnum::getOptionArray();
    }
}
