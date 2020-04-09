<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use EffectConnect\Marketplaces\Enums\ExternalFulfilment as ExternalFulfilmentEnum;

/**
 * Class ExternalFulfilment
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class ExternalFulfilment implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return ExternalFulfilmentEnum::getOptionArray();
    }
}
