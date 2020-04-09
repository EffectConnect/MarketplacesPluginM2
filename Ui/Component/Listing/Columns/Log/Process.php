<?php

namespace EffectConnect\Marketplaces\Ui\Component\Listing\Columns\Log;

use EffectConnect\Marketplaces\Enums\Process as ProcessEnum;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Process
 * @package EffectConnect\Marketplaces\Ui\Component\Listing\Columns\Log
 */
class Process extends Column
{
    /**
     * {@inheritdoc}
     */
    const NAME                          = 'process';

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
                if (!isset($item['process'])) {
                    $item['process']    = '-';
                    continue;
                }

                if (!ProcessEnum::isValid($item['process'])) {
                    $item['process']    = '-';
                    continue;
                }

                $process                = (new ProcessEnum($item['process']));
                $item['process']        = $process->getLabel();
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
                'process',
                strtoupper($sorting['direction'])
            );
        }
    }
}
