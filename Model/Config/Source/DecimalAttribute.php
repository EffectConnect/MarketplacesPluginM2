<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

/**
 * Class DecimalAttribute
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class DecimalAttribute extends Attribute
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray(bool $optional = false)
    {
        return array_filter(parent::toOptionArray($optional), function ($item) use ($optional) {
            $dataType = $item['type']['data'] ?? false;
            return $dataType === 'decimal' || ($optional && $dataType === 'none');
        });
    }
}