<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

/**
 * Class OptionalAttribute
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class OptionalAttribute extends Attribute
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray(bool $optional = true)
    {
        return parent::toOptionArray(true);
    }
}