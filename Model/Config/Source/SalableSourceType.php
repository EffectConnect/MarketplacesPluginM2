<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Enums\SalableSourceType as SalableSourceTypeEnum;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class SalableSourceType
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class SalableSourceType implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return SalableSourceTypeEnum::getOptionArray();
    }
}