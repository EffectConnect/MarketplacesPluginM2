<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Enums\LogExpiration as LogExpirationEnum;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class LogExpiration
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class LogExpiration implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return LogExpirationEnum::getOptionArray();
    }
}
