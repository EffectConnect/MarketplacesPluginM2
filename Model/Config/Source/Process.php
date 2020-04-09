<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Enums\Process as ProcessEnum;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class SubjectType
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class Process implements OptionSourceInterface
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
        $options        = ProcessEnum::getOptionArray();
        array_unshift($options, $emptyOption);
        return $options;
    }
}
