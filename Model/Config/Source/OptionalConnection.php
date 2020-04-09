<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

/**
 * Class OptionalConnection
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class OptionalConnection extends Connection
{
   /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $emptyOption    = [
            'label'     => ' ',
            'value'     => ''
        ];
        $options        = parent::toOptionArray();
        array_unshift($options, $emptyOption);
        return $options;
    }
}
