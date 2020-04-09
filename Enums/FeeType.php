<?php

namespace EffectConnect\Marketplaces\Enums;

/**
 * Class FeeType
 * @package EffectConnect\Marketplaces\Enums
 * @method static FeeType COMMISSION()
 * @method static FeeType SHIPPING()
 * @method static FeeType TRANSACTION()
 */
class FeeType extends AbstractEnum
{
    /**
     * Commission fee type.
     */
    const COMMISSION    = 'commission';

    /**
     * Shipping fee type.
     */
    const SHIPPING      = 'shipping';

    /**
     * Transaction fee type.
     */
    const TRANSACTION   = 'transaction';
}