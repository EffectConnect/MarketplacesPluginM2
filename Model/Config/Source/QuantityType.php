<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Enums\QuantityType as QuantityTypeEnum;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class QuantityType
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class QuantityType implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return QuantityTypeEnum::getOptionArray();
    }
}