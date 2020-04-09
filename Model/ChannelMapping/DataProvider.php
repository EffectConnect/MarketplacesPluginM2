<?php

namespace EffectConnect\Marketplaces\Model\ChannelMapping;

use EffectConnect\Marketplaces\Model\ChannelMapping;
use EffectConnect\Marketplaces\Model\ResourceModel\ChannelMapping\Collection as ChannelMappingCollection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProvider
 * @package EffectConnect\Marketplaces\Model\ChannelMapping
 */
class DataProvider extends AbstractDataProvider implements ScopeInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var PoolInterface
     */
    protected $_pool;

    /**
     * DataProvider constructor.
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param RequestInterface $request
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param ChannelMappingCollection $channelMappingCollection
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ScopeConfigInterface $scopeConfig,
        ChannelMappingCollection $channelMappingCollection,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    )
    {
        $this->_scopeConfig                   = $scopeConfig;
        $this->_request                       = $request;
        $this->_searchCriteriaBuilder         = $searchCriteriaBuilder;
        $this->_pool                          = $pool;
        $this->collection                     = $channelMappingCollection; // Default Magento variable in parent class
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

        $channelMappings = $this->collection->getItems();
        $this->loadedData = [];

        /* @var ChannelMapping $channelMapping */
        foreach ($channelMappings as $channelMapping) {
            $this->loadedData[$channelMapping->getId()] = $channelMapping->getData();
        }

        return $this->loadedData;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getMeta(): array
    {
        $meta = parent::getMeta();
        foreach ($this->_pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }
        return $meta;
    }
}
