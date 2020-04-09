<?php

namespace EffectConnect\Marketplaces\Enums;

/**
 * Class YesNo
 * @package EffectConnect\Marketplaces\Enums
 * @method static YesNo YES()
 * @method static YesNo NO()
 */
class YesNo extends AbstractEnum
{
    /**
     * Yes (true) value.
     */
    const YES   = 1;

    /**
     * No (false) value.
     */
    const NO    = 0;

    /**
     * {@inheritdoc}
     */
    public function getLabel() : string
    {
        return boolval($this->getValue()) ? __('Yes') : __('No');
    }

    /**
     * Get the boolean value for the current instance.
     *
     * @return string
     */
    public function getBooleanValue() : string
    {
        return boolval($this->getValue());
    }
}