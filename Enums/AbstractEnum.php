<?php

namespace EffectConnect\Marketplaces\Enums;

use Laminas\Filter\Word\UnderscoreToSeparator;
use MyCLabs\Enum\Enum;

/**
 * Abstract class LogCode
 * @package EffectConnect\Marketplaces\Enums
 */
abstract class AbstractEnum extends Enum
{
    /**
     * Get the translated label.
     *
     * @return string
     */
    public function getLabel() : string
    {
        return __(ucwords((new UnderscoreToSeparator())->filter($this->getValue())));
    }

    /**
     * Get options array (used for select fields).
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public static function getOptionArray() : array
    {
        $options = [];

        foreach (static::values() as $value) {
            $instance = new static($value);
            $options[] = [
                'label' => $instance->getLabel(),
                'value' => $instance->getValue()
            ];
        }

        return $options;
    }
}