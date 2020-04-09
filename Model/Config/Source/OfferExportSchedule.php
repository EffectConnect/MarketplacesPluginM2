<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class OfferExportSchedule
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class OfferExportSchedule implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray($type = false)
    {
        return [
            [
                'value' => 'disabled',
                'label' => __('Disabled')
            ],
            [
                'value' => '*/5 * * * *',
                'label' => sprintf(__('Every %s minutes'), '5') . ' (' . __("starting at 0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50 and 55 minutes past the hour") . ')'
            ],
            [
                'value' => '*/15 * * * *',
                'label' => sprintf(__('Every %s minutes'), '15') . ' (' . __("starting at 0, 15, 30 and 45 minutes past the hour") . ')'
            ],
            [
                'value' => '*/30 * * * *',
                'label' => sprintf(__('Every %s minutes'), '30') . ' (' . __("starting at 0 and 30 minutes past the hour") . ')'
            ],
            [
                'value' => '0 * * * *',
                'label' => __('Every hour') . ' (' . __("starting at 0 minutes past the hour") . ')'
            ],
            [
                'value' => '0 21 * * *',
                'label' => __('Every day') . ' (' . __("starting at 21:00 [9:00 PM]") . ')'
            ],
            [
                'value' => 'custom',
                'label' => __('Custom schedule (using cron expression format)')
            ],
        ];
    }
}