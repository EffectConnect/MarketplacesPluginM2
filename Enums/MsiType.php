<?php

namespace EffectConnect\Marketplaces\Enums;

/**
 * Class MsiType
 * @package EffectConnect\Marketplaces\Enums
 * @method static MsiType SOURCES()
 * @method static MsiType STOCKS()
 */
class MsiType extends AbstractEnum
{
    /**
     * Obtain the physical quantity from a source.
     */
    const SOURCES   = 'sources';

    /**
     * Obtain the physical quantity from a stock.
     */
    const STOCKS    = 'stocks';
}