<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Enums\MsiType as MsiTypeEnum;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class MsiType
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class MsiType implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return MsiTypeEnum::getOptionArray();
    }
}