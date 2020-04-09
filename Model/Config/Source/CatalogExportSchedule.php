<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CatalogExportSchedule
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class CatalogExportSchedule implements OptionSourceInterface
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
                'value' => '0 * * * *',
                'label' => __('Every hour') . ' (' . __("starting at 0:00") . ')'
            ],
            [
                'value' => '0 */2 * * *',
                'label' => sprintf(__('Every %s hours'), '2') . ' (' . __("starting at 0:00") . ')'
            ],
            [
                'value' => '0 */3 * * *',
                'label' => sprintf(__('Every %s hours'), '3') . ' (' . __("starting at 0:00") . ')'
            ],
            [
                'value' => '0 */6 * * *',
                'label' => sprintf(__('Every %s hours'), '6') . ' (' . __("starting at 0:00") . ')'
            ],
            [
                'value' => '0 */12 * * *',
                'label' => sprintf(__('Every %s hours'), '12') . ' (' . __("starting at 0:00") . ')'
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