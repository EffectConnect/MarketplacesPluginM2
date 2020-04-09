<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

/**
 * Class OptionalDecimalAttribute
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class OptionalDecimalAttribute extends DecimalAttribute
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray(bool $optional = true)
    {
        return parent::toOptionArray(true);
    }
}