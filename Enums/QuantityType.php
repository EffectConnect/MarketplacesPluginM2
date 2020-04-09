<?php

namespace EffectConnect\Marketplaces\Enums;

/**
 * Class QuantityType
 * @package EffectConnect\Marketplaces\Enums
 * @method static QuantityType TRADITIONAL()
 * @method static QuantityType SALABLE()
 * @method static QuantityType PHYSICAL()
 */
class QuantityType extends AbstractEnum
{
    /**
     * Traditional method to obtain the quantity for product (used in Legacy Magento 2 - prior to version 2.3).
     */
    const TRADITIONAL   = 'traditional';

    /**
     * Salable quantity method subtracts the quantity of products in open orders from the physical quantity (available from Magento 2 version 2.3).
     */
    const SALABLE       = 'salable';

    /**
     * Salable quantity method obtains the physical quantity of products and does not take into account the quantity of products in open orders (available from Magento 2 version 2.3).
     */
    const PHYSICAL      = 'physical';
}