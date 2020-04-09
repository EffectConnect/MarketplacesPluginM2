<?php

namespace EffectConnect\Marketplaces\Model\Connection;

use EffectConnect\Marketplaces\Api\ConnectionRepositoryInterface;
use EffectConnect\Marketplaces\Api\ConnectionStoreviewRepositoryInterface;
use EffectConnect\Marketplaces\Model\Connection;
use EffectConnect\Marketplaces\Model\ConnectionStoreview;
use EffectConnect\Marketplaces\Model\ConnectionFactory as ConnectionFactory;
use EffectConnect\Marketplaces\Model\ResourceModel\Connection as ConnectionResource;
use EffectConnect\Marketplaces\Model\ResourceModel\ConnectionStoreview\Collection as ConnectionStoreviewCollection;
use EffectConnect\Marketplaces\Model\ResourceModel\Connection\Collection as ConnectionCollection;
use EffectConnect\Marketplaces\Model\ResourceModel\Connection\CollectionFactory as ConnectionCollectionFactory;
use Exception;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ConnectionRepository
 * @package EffectConnect\Marketplaces\Model\Connection
 */
class ConnectionRepository implements ConnectionRepositoryInterface
{
    /**
     * @var ConnectionFactory
     */
    protected $_connectionFactory;

    /**
     * @var ConnectionResource
     */
    protected $_connectionResource;

    /**
     * @var ConnectionStoreviewCollection
     */
    protected $_connectionStoreviewCollection;

    /**
     * @var ConnectionStoreviewRepositoryInterface
     */
    protected $_connectionStoreviewRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var SearchResultFactory
     */
    protected $_searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $_collectionProcessor;

    /**
     * @var ConnectionCollectionFactory
     */
    protected $_connectionCollectionFactory;

    /**
     * ConnectionRepository constructor.
     * @param ConnectionStoreviewCollection $connectionStoreviewCollection
     * @param ConnectionCollectionFactory $connectionCollectionFactory
     * @param ConnectionFactory $connectionFactory
     * @param ConnectionResource $connectionResource
     * @param ConnectionStoreviewRepositoryInterface $connectionStoreviewRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ConnectionStoreviewCollection $connectionStoreviewCollection,
        ConnectionCollectionFactory $connectionCollectionFactory,
        ConnectionFactory $connectionFactory,
        ConnectionResource $connectionResource,
        ConnectionStoreviewRepositoryInterface $connectionStoreviewRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->_connectionFactory             = $connectionFactory;
        $this->_connectionResource            = $connectionResource;
        $this->_connectionCollectionFactory   = $connectionCollectionFactory;
        $this->_connectionStoreviewCollection = $connectionStoreviewCollection;
        $this->_connectionStoreviewRepository = $connectionStoreviewRepository;
        $this->_searchCriteriaBuilder         = $searchCriteriaBuilder;
        $this->_searchResultFactory           = $searchResultFactory;
        $this->_collectionProcessor           = $collectionProcessor;
    }

    /**
     * @return Connection
     */
    public function create() : Connection
    {
        $connection = $this->_connectionFactory->create();
        return $connection;
    }

    /**
     * @param int $connectionId
     * @return Connection
     * @throws NoSuchEntityException
     */
    public function getById(int $connectionId) : Connection
    {
        $connection = $this->create();
        $this->_connectionResource->load($connection, $connectionId);
        if (!$connection->getId()) {
            throw new NoSuchEntityException(__('Connection with specified ID %1 not found.', $connectionId));
        }
        return $connection;
    }

    /**
     * @param int $connectionId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $connectionId) : bool
    {
        $connectionModel = $this->getById($connectionId);
        $this->delete($connectionModel);
        return true;
    }

    /**
     * @param Connection $connection
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Connection $connection) : bool
    {
        try {
            $this->_connectionResource->delete($connection);
        } catch (Exception $e) {
            if ($connection->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove connection with ID %1. Error: %2',
                        [$connection->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove connection. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * @param Connection $connection
     * @param ConnectionStoreview[] $connectionStoreviews
     * @return Connection
     * @throws CouldNotSaveException
     */
    public function save(Connection $connection, array $connectionStoreviews) : Connection
    {
        try
        {
            // Save connection
            $this->_connectionResource->save($connection);

            // Remove existing connection storeviews
            $searchCriteria = $this->_searchCriteriaBuilder
                ->addFilter(
                    'connection_id',
                    $connection->getId()
                )
                ->create();
            $existingConnectionStoreviews = $this->_connectionStoreviewRepository->getList($searchCriteria)->getItems();
            foreach ($existingConnectionStoreviews as $existingConnectionStoreview) {
                $this->_connectionStoreviewRepository->delete($existingConnectionStoreview);
            }

            // Add new connection storeviews
            foreach ($connectionStoreviews as $connectionStoreview) {
                $connectionStoreview->addData(['connection_id' => $connection->getId()]);
                $this->_connectionStoreviewRepository->save($connectionStoreview);
            }
        }
        catch (Exception $e)
        {
            if ($connection->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save connection with ID %1. Error: %2',
                        [$connection->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save connection. Error: %1', $e->getMessage()));
        }
        return $connection;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null) : SearchResultsInterface
    {
        /** @var ConnectionCollection $collection */
        $collection     = $this->_connectionCollectionFactory->create();

        if (is_null($searchCriteria)) {
            $searchCriteria = $this->_searchCriteriaBuilder->create();
        }

        $this->_collectionProcessor->process($searchCriteria, $collection);

        $collection->load();

        $searchResult   = $this->_searchResultFactory->create();

        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }
}
