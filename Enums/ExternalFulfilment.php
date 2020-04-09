<?php

namespace EffectConnect\Marketplaces\Enums;

/**
 * Class ExternalFulfilment
 * @package EffectConnect\Marketplaces\Enums
 * @method static ExternalFulfilment INTERNAL_ORDERS()
 * @method static ExternalFulfilment EXTERNAL_ORDERS()
 * @method static ExternalFulfilment EXTERNAL_AND_INTERNAL_ORDERS()
 */
class ExternalFulfilment extends AbstractEnum
{
    /**
     * Only import my own orders.
     */
    const INTERNAL_ORDERS = 'internal_orders';

    /**
     * Only import orders that are fulfilled externally by the channel.
     */
    const EXTERNAL_ORDERS = 'external_orders';

    /**
     * Import both my own order and externally fulfilled orders.
     */
    const EXTERNAL_AND_INTERNAL_ORDERS = 'external_and_internal_orders';

    /**
     * @return array
     */
    public static function getOptionArray() : array
    {
        return [
            [
                'label' => __('Only import internal orders'),
                'value' => self::INTERNAL_ORDERS()
            ],
            [
                'label' => __('Only import orders that are fulfilled externally'),
                'value' => self::EXTERNAL_ORDERS()
            ],
            [
                'label' => __('Import both internal orders and orders that are fulfilled externally'),
                'value' => self::EXTERNAL_AND_INTERNAL_ORDERS()
            ]
        ];
    }
}