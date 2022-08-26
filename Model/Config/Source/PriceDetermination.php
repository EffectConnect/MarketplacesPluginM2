<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PriceDetermination
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class PriceDetermination implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('Default Magento price determination (including catalog sales rules)')
            ],
            [
                'value' => 0,
                'label' => __('Advanced price determination')
            ],
        ];
    }
}