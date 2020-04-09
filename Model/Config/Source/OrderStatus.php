<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Sales\Model\Config\Source\Order\Status;

/**
 * Class OrderStatus
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class OrderStatus extends Status
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();

        if (isset($options[0])) {
            unset($options[0]);
        }

        return $options;
    }
}
