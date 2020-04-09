<?php

namespace EffectConnect\Marketplaces\Model\ConnectionStoreview;

use EffectConnect\Marketplaces\Api\ConnectionStoreviewRepositoryInterface;
use EffectConnect\Marketplaces\Model\ConnectionStoreview;
use EffectConnect\Marketplaces\Model\ConnectionStoreviewFactory;
use EffectConnect\Marketplaces\Model\ResourceModel\ConnectionStoreview as ConnectionStoreviewResource;
use EffectConnect\Marketplaces\Model\ResourceModel\ConnectionStoreview\Collection as ConnectionStoreviewCollection;
use EffectConnect\Marketplaces\Model\ResourceModel\ConnectionStoreview\CollectionFactory as ConnectionStoreviewCollectionFactory;
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
 * Class ConnectionStoreviewRepository
 * @package EffectConnect\Marketplaces\Model\ConnectionStoreview
 */
class ConnectionStoreviewRepository implements ConnectionStoreviewRepositoryInterface
{
    /**
     * @var ConnectionStoreviewFactory
     */
    protected $_connectionStoreviewFactory;

    /**
     * @var ConnectionStoreviewResource
     */
    protected $_connectionStoreviewResource;

    /**
     * @var ConnectionStoreviewCollection
     */
    protected $_connectionStoreviewCollection;

    /**
     * @var SearchResultFactory
     */
    protected $_searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $_collectionProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var ConnectionStoreviewCollectionFactory
     */
    protected $_connectionStoreviewCollectionFactory;

    /**
     * ConnectionStoreviewRepository constructor.
     * @param ConnectionStoreviewFactory $connectionStoreviewFactory
     * @param ConnectionStoreviewResource $connectionStoreviewResource
     * @param ConnectionStoreviewCollection $connectionStoreviewCollection
     * @param ConnectionStoreviewCollectionFactory $connectionStoreviewCollectionFactory
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ConnectionStoreviewFactory $connectionStoreviewFactory,
        ConnectionStoreviewResource $connectionStoreviewResource,
        ConnectionStoreviewCollection $connectionStoreviewCollection,
        ConnectionStoreviewCollectionFactory $connectionStoreviewCollectionFactory,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->_connectionStoreviewFactory           = $connectionStoreviewFactory;
        $this->_connectionStoreviewResource          = $connectionStoreviewResource;
        $this->_connectionStoreviewCollection        = $connectionStoreviewCollection;
        $this->_connectionStoreviewCollectionFactory = $connectionStoreviewCollectionFactory;
        $this->_searchResultFactory                  = $searchResultFactory;
        $this->_collectionProcessor                  = $collectionProcessor;
        $this->_searchCriteriaBuilder                = $searchCriteriaBuilder;
    }

    /**
     * @return ConnectionStoreview
     */
    public function create() : ConnectionStoreview
    {
        $connection = $this->_connectionStoreviewFactory->create();
        return $connection;
    }

    /**
     * @param ConnectionStoreview $connectionStoreview
     * @return ConnectionStoreview
     * @throws CouldNotSaveException
     */
    public function save(ConnectionStoreview $connectionStoreview) : ConnectionStoreview
    {
        try {
            $this->_connectionStoreviewResource->save($connectionStoreview);
        } catch (Exception $e) {
            if ($connectionStoreview->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save connection storeview with ID %1. Error: %2',
                        [$connectionStoreview->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save connection storeview. Error: %1', $e->getMessage()));
        }
        return $connectionStoreview;
    }

    /**
     * @param int $connectionStoreviewId
     * @return ConnectionStoreview
     * @throws NoSuchEntityException
     */
    public function getById(int $connectionStoreviewId) : ConnectionStoreview
    {
        $connectionStoreview = $this->create();
        $this->_connectionStoreviewResource->load($connectionStoreview, $connectionStoreviewId);
        if (!$connectionStoreview->getId()) {
            throw new NoSuchEntityException(__('Connection storeview with specified ID %1 not found.', $connectionStoreviewId));
        }
        return $connectionStoreview;
    }

    /**
     * @param int $connectionStoreviewId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $connectionStoreviewId) : bool
    {
        $connectionStoreviewModel = $this->getById($connectionStoreviewId);
        $this->delete($connectionStoreviewModel);
    }

    /**
     * @param ConnectionStoreview $connectionStoreview
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ConnectionStoreview $connectionStoreview) : bool
    {
        try {
            $this->_connectionStoreviewResource->delete($connectionStoreview);
        } catch (Exception $e) {
            if ($connectionStoreview->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove connection storeview with ID %1. Error: %2',
                        [$connectionStoreview->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove connection storeview. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null) : SearchResultsInterface
    {
        if (is_null($searchCriteria)) {
            $searchCriteria = $this->_searchCriteriaBuilder->create();
        }

        $connectionStoreviewCollection = $this->_connectionStoreviewCollectionFactory->create();
        $this->_collectionProcessor->process($searchCriteria, $connectionStoreviewCollection);
        $this->_connectionStoreviewCollection->load();
        $searchResult = $this->_searchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($connectionStoreviewCollection->getItems());
        $searchResult->setTotalCount($connectionStoreviewCollection->getSize());

        return $searchResult;
    }

    /**
     * @param int $connectionId
     * @return SearchResultsInterface
     */
    public function getListByConnectionId(int $connectionId) : SearchResultsInterface
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(
                'connection_id',
                $connectionId
            )
            ->create();
        return $this->getList($searchCriteria);
    }
}
