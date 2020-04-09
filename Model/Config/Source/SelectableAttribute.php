<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

/**
 * Class SelectableAttribute
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class SelectableAttribute extends Attribute
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray(bool $optional = false)
    {
        return array_filter(parent::toOptionArray($optional), function ($item) use ($optional) {
            $fieldType = $item['type']['field'] ?? false;
            return $fieldType === 'select' || ($optional && $fieldType === 'none');
        });
    }
}