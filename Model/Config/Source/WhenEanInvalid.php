<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Enums\WhenEanInvalid as WhenEanInvalidEnum;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class WhenEanInvalid
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class WhenEanInvalid implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return WhenEanInvalidEnum::getOptionArray();
    }
}