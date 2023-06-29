<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Sales\Model\Config\Source\Order\Status;

/**
 * Class OptionalOrderStatus
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class OptionalOrderStatus extends Status
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();

        // Remove 'Please select'
        if (isset($options[0])) {
            unset($options[0]);
        }

        // Add 'Use default configuration'
        array_unshift($options, [
            'value' => '',
            'label' => __('Use default configuration'),
        ]);

        return $options;
    }
}
