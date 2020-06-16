<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Config\Model\Config\Source\Yesnocustom;

/**
 * Class OptionalYesNo
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class OptionalYesNo extends Yesnocustom
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array_map(function($item) {
            if (intval($item['value']) === 2) {
                $item['label'] = __('Use default configuration');
            }
            return $item;
        }, parent::toOptionArray());
    }
}