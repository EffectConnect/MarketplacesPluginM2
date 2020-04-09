<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

/**
 * Class OptionalMultilineAttribute
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class OptionalMultilineAttribute extends MultilineAttribute
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray(bool $optional = true)
    {
        return parent::toOptionArray(true);
    }
}