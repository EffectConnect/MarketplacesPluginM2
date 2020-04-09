<?php

namespace EffectConnect\Marketplaces\Ui\Component\Listing\Columns\Log;

use EffectConnect\Marketplaces\Enums\LogCode as LogCodeEnum;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Code
 * @package EffectConnect\Marketplaces\Ui\Component\Listing\Columns\Log
 */
class Code extends Column
{
    /**
     * {@inheritdoc}
     */
    const NAME                      = 'code';

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!isset($item['code'])) {
                    $item['code']       = '-';
                    continue;
                }

                if (!LogCodeEnum::isValid($item['code'])) {
                    $item['code']       = '-';
                    continue;
                }

                $process                = (new LogCodeEnum($item['code']));
                $item['code']           = $process->getLabel();
            }
        }

        return $dataSource;
    }

    /**
     * {@inheritdoc}
     */
    protected function applySorting()
    {
        $sorting    = $this->getContext()->getRequestParam('sorting');
        $isSortable = $this->getData('config/sortable');

        if (
            $isSortable !== false &&
            !empty($sorting['field']) &&
            !empty($sorting['direction']) &&
            $sorting['field'] == static::getName()
        ) {
            $this->getContext()->getDataProvider()->addOrder(
                'code',
                strtoupper($sorting['direction'])
            );
        }
    }
}
