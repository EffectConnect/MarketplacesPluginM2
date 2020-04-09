<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

/**
 * Class OptionalPaymentMethods
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class OptionalPaymentMethods extends PaymentMethods
{
    /**
     * @param bool $type
     * @return array
     */
    public function toOptionArray($type = false)
    {
        $options = parent::toOptionArray($type);

        // For UI component form select, we need numerical keys for values within an optgroup.
        foreach ($options as &$option) {
            if (is_array($option['value'])) {
                $option['value'] = array_values($option['value']);
            }
        }

        // Prepend extra option.
        array_unshift($options, [
            'value' => '',
            'label' => __('Use default configuration'),
        ]);

        return $options;
    }
}