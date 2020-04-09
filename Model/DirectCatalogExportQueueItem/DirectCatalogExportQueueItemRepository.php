<?php

namespace EffectConnect\Marketplaces\Model\DirectCatalogExportQueueItem;

use EffectConnect\Marketplaces\Api\DirectCatalogExportQueueItemRepositoryInterface;
use EffectConnect\Marketplaces\Model\DirectCatalogExportQueueItem;
use EffectConnect\Marketplaces\Model\DirectCatalogExportQueueItemFactory;
use EffectConnect\Marketplaces\Model\ResourceModel\DirectCatalogExportQueueItem as DirectCatalogExportQueueItemResource;
use EffectConnect\Marketplaces\Model\ResourceModel\DirectCatalogExportQueueItem\Collection as DirectCatalogExportQueueItemCollection;
use EffectConnect\Marketplaces\Model\ResourceModel\DirectCatalogExportQueueItem\CollectionFactory as DirectCatalogExportQueueItemCollectionFactory;
use Exception;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class DirectCatalogExportQueueItemRepository
 * @package EffectConnect\Marketplaces\Model\DirectCatalogExportQueueItem
 */
class DirectCatalogExportQueueItemRepository implements DirectCatalogExportQueueItemRepositoryInterface
{
    /**
     * @var DirectCatalogExportQueueItem
     */
    protected $_directCatalogExportQueueItemFactory;

    /**
     * @var DirectCatalogExportQueueItemResource
     */
    protected $_directCatalogExportQueueItemResource;

    /**
     * @var DirectCatalogExportQueueItemCollection
     */
    protected $_directCatalogExportQueueItemCollectionFactory;

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
     * @var SortOrderBuilder
     */
    protected $_sortOrderBuilder;

    /**
     * DirectCatalogExportQueueItemRepository constructor.
     *
     * @param DirectCatalogExportQueueItemFactory $directCatalogExportQueueItemFactory
     * @param DirectCatalogExportQueueItemResource $directCatalogExportQueueItemResource
     * @param DirectCatalogExportQueueItemCollectionFactory $directCatalogExportQueueItemCollectionFactory
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        DirectCatalogExportQueueItemFactory $directCatalogExportQueueItemFactory,
        DirectCatalogExportQueueItemResource $directCatalogExportQueueItemResource,
        DirectCatalogExportQueueItemCollectionFactory $directCatalogExportQueueItemCollectionFactory,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->_directCatalogExportQueueItemFactory             = $directCatalogExportQueueItemFactory;
        $this->_directCatalogExportQueueItemResource            = $directCatalogExportQueueItemResource;
        $this->_directCatalogExportQueueItemCollectionFactory   = $directCatalogExportQueueItemCollectionFactory;
        $this->_searchResultFactory                             = $searchResultFactory;
        $this->_collectionProcessor                             = $collectionProcessor;
        $this->_searchCriteriaBuilder                           = $searchCriteriaBuilder;
        $this->_sortOrderBuilder                                = $sortOrderBuilder;
    }

    /**
     * Load DirectCatalogExportQueueItem entity by ID.
     *
     * @param int $id
     * @return DirectCatalogExportQueueItem
     * @throws NoSuchEntityException
     */
    public function getById(int $id) : DirectCatalogExportQueueItem
    {
        /** @var DirectCatalogExportQueueItem $directCatalogExportQueueItem */
        $directCatalogExportQueueItem   = $this->_directCatalogExportQueueItemFactory->create();

        $this->_directCatalogExportQueueItemResource->load($directCatalogExportQueueItem, $id);

        if (!$directCatalogExportQueueItem->getId()) {
            throw new NoSuchEntityException(__('DirectCatalogExportQueueItem entry with specified ID %1 not found.', $id));
        }

        return $directCatalogExportQueueItem;
    }

    /**
     * Check if a non-executed queue item with a certain connection id is in the queue table.
     *
     * @param int $productId
     * @return bool
     */
    public function isNonExecutedConnectionPresent(int $productId) : bool
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter('connection_entity_id', $productId)
            ->addFilter('executed_at', null, 'null')
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        return $this->getList($searchCriteria)->getTotalCount() > 0;
    }

    /**
     * Create a new DirectCatalogExportQueueItem entity.
     *
     * @return DirectCatalogExportQueueItem
     */
    public function create() : DirectCatalogExportQueueItem
    {
        return $this->_directCatalogExportQueueItemFactory->create();
    }

    /**
     * Lists DirectCatalogExportQueueItem entries that match specified search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : SearchResultsInterface
    {
        /** @var DirectCatalogExportQueueItemCollection $collection */
        $collection     = $this->_directCatalogExportQueueItemCollectionFactory->create();

        $this->_collectionProcessor->process($searchCriteria, $collection);

        $collection->load();

        $searchResult   = $this->_searchResultFactory->create();

        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Lists oldest DirectCatalogExportQueueItem entry that is not executed yet.
     *
     * @return SearchResultsInterface
     */
    public function getOldestUnexecuted() : SearchResultsInterface
    {
        $sortOrder      = $this->_sortOrderBuilder
            ->setField('created_at')
            ->setDescendingDirection()
            ->create();

        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(
                'executed_at',
                null,
                'null'
            )->addSortOrder($sortOrder)
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        return $this->getList($searchCriteria);
    }

    /**
     * Delete a DirectCatalogExportQueueItem entity.
     *
     * @param DirectCatalogExportQueueItem $directCatalogExportQueueItem
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(DirectCatalogExportQueueItem $directCatalogExportQueueItem) : bool
    {
        try {
            $this->_directCatalogExportQueueItemResource->delete($directCatalogExportQueueItem);
        } catch (Exception $e) {
            if ($directCatalogExportQueueItem->getId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove DirectCatalogExportQueueItem entry with ID %1. Error: %2', [
                        $directCatalogExportQueueItem->getId(),
                        $e->getMessage()
                    ])
                );
            }

            throw new CouldNotDeleteException(__('Unable to remove DirectCatalogExportQueueItem entry. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * Delete a DirectCatalogExportQueueItem entity by ID.
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $id) : bool
    {
        $directCatalogExportQueueItemModel = $this->getById($id);
        $this->delete($directCatalogExportQueueItemModel);

        return true;
    }

    /**
     * Save a DirectCatalogExportQueueItem entity.
     *
     * @param DirectCatalogExportQueueItem $directCatalogExportQueueItem
     * @return DirectCatalogExportQueueItem
     * @throws CouldNotSaveException
     */
    public function save(DirectCatalogExportQueueItem $directCatalogExportQueueItem) : DirectCatalogExportQueueItem
    {
        try {
            $this->_directCatalogExportQueueItemResource->save($directCatalogExportQueueItem);
        } catch (Exception $e) {
            if ($directCatalogExportQueueItem->getId()) {
                throw new CouldNotSaveException(
                    __('Unable to save DirectCatalogExportQueueItem entry with ID %1. Error: %2', [
                        $directCatalogExportQueueItem->getId(),
                        $e->getMessage()
                    ])
                );
            }

            throw new CouldNotSaveException(__('Unable to save new DirectCatalogExportQueueItem entry. Error: %1', $e->getMessage()));
        }

        return $directCatalogExportQueueItem;
    }
}
