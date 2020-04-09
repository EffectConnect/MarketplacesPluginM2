<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

/**
 * Class OptionalSelectableAttribute
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class OptionalSelectableAttribute extends SelectableAttribute
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray(bool $optional = true)
    {
        return parent::toOptionArray(true);
    }
}