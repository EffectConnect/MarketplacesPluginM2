<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Enums\LogSubjectType;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class SubjectType
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class SubjectType implements OptionSourceInterface
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
        $options        = LogSubjectType::getOptionArray();
        array_unshift($options, $emptyOption);
        return $options;
    }
}
