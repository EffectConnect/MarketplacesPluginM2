<?php

namespace EffectConnect\Marketplaces\Model\Log;

use Magento\Ui\DataProvider\AbstractDataProvider;
use EffectConnect\Marketplaces\Model\ResourceModel\Log\Collection;

/**
 * Class DataProvider
 * @package EffectConnect\Marketplaces\Model\Log
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Collection $collection
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $collection;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        $this->loadedData = array();
        foreach ($items as $item) {
            $this->loadedData[$item->getId()]['log'] = $item->getData();
        }

        return $this->loadedData;
    }
}
