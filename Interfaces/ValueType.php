<?php

namespace EffectConnect\Marketplaces\Interfaces;

/**
 * Contains all possible value types accepted for the EffectConnect Marketplaces SDK.
 *
 * Interface ValueType
 * @package EffectConnect\Marketplaces\Interfaces
 */
interface ValueType
{
    /** String value. */
    const VALUE_TYPE_STRING = 'str';

    /** Integer value. */
    const VALUE_TYPE_INT    = 'int';

    /** Float value. */
    const VALUE_TYPE_FLOAT  = 'float';

    /** Boolean value. */
    const VALUE_TYPE_BOOL   = 'bool';

    /** Array value. */
    const VALUE_TYPE_ARRAY  = 'array';
}