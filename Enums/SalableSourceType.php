<?php

namespace EffectConnect\Marketplaces\Enums;

/**
 * Class SalableSourceType
 * @package EffectConnect\Marketplaces\Enums
 * @method static SalableSourceType WEBSITE()
 * @method static SalableSourceType STOCK()
 */
class SalableSourceType extends AbstractEnum
{
    /**
     * Obtain the salable quantity from a website.
     */
    const WEBSITE   = 'website';

    /**
     * Obtain the salable quantity from a stock.
     */
    const STOCK     = 'stock';
}