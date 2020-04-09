<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class EnabledDisabled
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class EnabledDisabled implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('Enabled')
            ],
            [
                'value' => 0,
                'label' => __('Disabled')
            ]
        ];
    }
}