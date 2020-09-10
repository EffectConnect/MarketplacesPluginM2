<?php

namespace EffectConnect\Marketplaces\Enums;

/**
 * Class WhenEanInvalid
 * @package EffectConnect\Marketplaces\Enums
 * @method static WhenEanInvalid YES()
 * @method static WhenEanInvalid NO()
 */
class WhenEanInvalid extends AbstractEnum
{
    /**
     * Export product(s) (without its EAN) (true) value.
     */
    const EXPORT        = 1;

    /**
     * Don't export product(s) (false) value.
     */
    const DONT_EXPORT   = 0;

    /**
     * {@inheritdoc}
     */
    public function getLabel() : string
    {
        return boolval($this->getValue()) ? __('Export product(s) (without its EAN)') : __('Don\'t export product(s)');
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