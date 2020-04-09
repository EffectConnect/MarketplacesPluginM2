<?php

namespace EffectConnect\Marketplaces\Ui\Component\Listing\Columns\Log;

use EffectConnect\Marketplaces\Enums\LogType;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Type
 * @package EffectConnect\Marketplaces\Ui\Component\Listing\Columns\Log
 */
class Type extends Column
{
    /**
     * {@inheritdoc}
     */
    const NAME                  = 'type';

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (!isset($item['type'])) {
                    continue;
                }

                if (!LogType::isValid($item['type'])) {
                    continue;
                }

                $item['type']   = (new LogType(strtolower($item['type'])))->getIndexColumnCell();
            }
        }

        return $dataSource;
    }
}
