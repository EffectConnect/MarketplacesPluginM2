<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Enums\LogCode as LogCodeEnum;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class LogCode
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class LogCode implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $emptyOption    = [
            'label'     => ' ',
            'value'     => ''
        ];
        $options        = LogCodeEnum::getOptionArray();
        array_unshift($options, $emptyOption);
        return $options;
    }
}
