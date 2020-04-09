<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

/**
 * Class MultilineAttribute
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class MultilineAttribute extends Attribute
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray(bool $optional = false)
    {
        return array_filter(parent::toOptionArray($optional), function ($item) use ($optional) {
            $fieldType = $item['type']['field'] ?? false;
            return $fieldType === 'textarea' || ($optional && $fieldType === 'none');
        });
    }
}