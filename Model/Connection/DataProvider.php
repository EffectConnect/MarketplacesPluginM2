<?php

namespace EffectConnect\Marketplaces\Model\Connection;

use EffectConnect\Marketplaces\Api\ConnectionStoreviewRepositoryInterface;
use EffectConnect\Marketplaces\Model\Connection;
use EffectConnect\Marketplaces\Model\ResourceModel\Connection\Collection as ConnectionCollection;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\System\Store;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProvider
 * @package EffectConnect\Marketplaces\Model\Connection
 */
class DataProvider extends AbstractDataProvider implements ScopeInterface
{
    /**
     * @var Store
     */
    protected $_store;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ConnectionStoreviewRepositoryInterface
     */
    protected $_connectionStoreviewRepository;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var PoolInterface
     */
    protected $_pool;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param Store $store
     * @param ScopeConfigInterface $scopeConfig
     * @param ConnectionCollection $connectionCollection
     * @param ConnectionStoreviewRepositoryInterface $connectionStoreviewRepository
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        Store $store,
        ScopeConfigInterface $scopeConfig,
        ConnectionCollection $connectionCollection,
        ConnectionStoreviewRepositoryInterface $connectionStoreviewRepository,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    )
    {
        $this->_store                         = $store;
        $this->_scopeConfig                   = $scopeConfig;
        $this->_request                       = $request;
        $this->_connectionStoreviewRepository = $connectionStoreviewRepository;
        $this->_pool                          = $pool;
        $this->collection                     = $connectionCollection; // Default Magento variable in parent class
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

        $connections = $this->collection->getItems();
        $this->loadedData = [];
        /* @var Connection $connection */
        foreach ($connections as $connection) {
            $this->loadedData[$connection->getId()] = $connection->getData();

            // Load storeview data on connection detail page
            $storeViewData = $this->getStoreviewMappingData($connection->getId());
            if ($storeViewData) {
                $this->loadedData[$connection->getId()]['storeview_mapping'] = $storeViewData;
            }
        }

        // Also load mappable storeviews for 'add' form.
        $this->loadedData[null]['storeview_mapping'] = $this->getStoreviewMappingData();

        return $this->loadedData;
    }

    /**
     * @return array
     */
    protected function getStoreviews()
    {
        $storeviews = [];

        foreach ($this->_store->getWebsiteCollection() as $website) {
            foreach ($this->_store->getGroupCollection() as $group) {
                if ($website->getId() != $group->getWebsiteId()) {
                    continue;
                }
                foreach ($this->_store->getStoreCollection() as $store) {
                    if ($group->getId() != $store->getGroupId()) {
                        continue;
                    }
                    $storeviewData               = $store->getData();
                    $storeviewData['group_name'] = $group->getName();
                    $storeviews[$store->getId()] = $storeviewData;
                }
            }
        }


        return $storeviews;
    }

    /**
     * @param int $connectionId
     * @return array
     */
    protected function getStoreviewMappingData(int $connectionId = 0) : array
    {
        $storeviewMappingData = [];
        $connectionStoreviews = $this->_connectionStoreviewRepository->getListByConnectionId($connectionId)->getItems();
        foreach ($this->getStoreviews() as $storeview) {

            // Default language code
            $languageCode = '';

            // Check if existing language code mapping exists for current connection
            foreach ($connectionStoreviews as $connectionStoreview) {
                $connectionStoreviewId = $connectionStoreview->getStoreviewId();
                if ($connectionStoreviewId == $storeview['store_id']) {
                    $languageCode = $connectionStoreview->getLanguageCode();
                }
            }

            // Add dynamic row
            $storeviewMappingData[] = [
                'website_id'        => $storeview['website_id'],
                'storeview_id'      => $storeview['store_id'],
                'storeview_name'    => $storeview['group_name'] . ' > ' . $storeview['name'],
                'language_code'     => $languageCode
            ];
        }

        return $storeviewMappingData;
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
