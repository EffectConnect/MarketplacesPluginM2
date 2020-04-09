<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Enums\LogType as LogTypeEnum;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class LogType
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class LogType implements OptionSourceInterface
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
        $options        = LogTypeEnum::getOptionArray();
        array_unshift($options, $emptyOption);
        return $options;
    }
}
