<?php

namespace EffectConnect\Marketplaces\Enums;

/**
 * Class ShipmentEvent
 * @package EffectConnect\Marketplaces\Enums
 * @method static ShipmentEvent SHIPMENT()
 * @method static ShipmentEvent TRACKING()
 */
class ShipmentEvent extends AbstractEnum
{
    /**
     * Update shipments to EffectConnect when a new shipment is created in Magento.
     */
    const SHIPMENT = 'shipment';

    /**
     * Update shipments to EffectConnect when a new tracking code is created in Magento.
     */
    const TRACKING = 'tracking';

    /**
     * @return array
     */
    public static function getOptionArray() : array
    {
        return [
            [
                'label' => __('When shipment is created'),
                'value' => self::SHIPMENT()
            ],
            [
                'label' => __('When tracking code is added to a shipment'),
                'value' => self::TRACKING()
            ],
        ];
    }
}