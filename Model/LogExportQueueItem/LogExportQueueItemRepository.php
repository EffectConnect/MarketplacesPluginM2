<?php

namespace EffectConnect\Marketplaces\Model\LogExportQueueItem;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SortOrderBuilder;
use EffectConnect\Marketplaces\Model\LogExportQueueItem;
use EffectConnect\Marketplaces\Model\LogExportQueueItemFactory;
use EffectConnect\Marketplaces\Model\ResourceModel\LogExportQueueItem as LogExportQueueItemResource;
use EffectConnect\Marketplaces\Model\ResourceModel\LogExportQueueItem\Collection as LogExportQueueItemCollection;
use EffectConnect\Marketplaces\Model\ResourceModel\LogExportQueueItem\CollectionFactory as LogExportQueueItemCollectionFactory;
use EffectConnect\Marketplaces\Api\LogExportQueueItemRepositoryInterface;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class LogExportQueueItemRepository
 * @package EffectConnect\Marketplaces\Model\LogExportQueueItem
 */
class LogExportQueueItemRepository implements LogExportQueueItemRepositoryInterface
{
    /**
     * @var LogExportQueueItemFactory
     */
    protected $_logExportQueueItemFactory;

    /**
     * @var LogExportQueueItemResource
     */
    protected $_logExportQueueItemResource;

    /**
     * @var LogExportQueueItemCollectionFactory
     */
    protected $_logExportQueueItemCollectionFactory;

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
     * LogExportQueueItemRepository constructor.
     * @param LogExportQueueItemFactory $logExportQueueItemFactory
     * @param LogExportQueueItemResource $logExportQueueItemResource
     * @param LogExportQueueItemCollectionFactory $logExportQueueItemCollectionFactory
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        LogExportQueueItemFactory $logExportQueueItemFactory,
        LogExportQueueItemResource $logExportQueueItemResource,
        LogExportQueueItemCollectionFactory $logExportQueueItemCollectionFactory,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->_logExportQueueItemFactory           = $logExportQueueItemFactory;
        $this->_logExportQueueItemResource          = $logExportQueueItemResource;
        $this->_logExportQueueItemCollectionFactory = $logExportQueueItemCollectionFactory;
        $this->_searchResultFactory                 = $searchResultFactory;
        $this->_collectionProcessor                 = $collectionProcessor;
        $this->_searchCriteriaBuilder               = $searchCriteriaBuilder;
        $this->_sortOrderBuilder                    = $sortOrderBuilder;
    }

    /**
     * Check if a non-executed queue item with a certain connection id is in the queue table.
     *
     * @param int $connectionId
     * @return bool
     */
    public function isNonExecutedConnectionPresent(int $connectionId) : bool
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter('connection_entity_id', $connectionId)
            ->addFilter('executed_at', null, 'null')
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        return $this->getList($searchCriteria)->getTotalCount() > 0;
    }

    /**
     * Create a new LogExportQueueItem entity.
     *
     * @return LogExportQueueItem
     */
    public function create() : LogExportQueueItem
    {
        return $this->_logExportQueueItemFactory->create();
    }

    /**
     * Lists LogExportQueueItem entries that match specified search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null) : SearchResultsInterface
    {
        if (is_null($searchCriteria)) {
            $searchCriteria = $this->_searchCriteriaBuilder->create();
        }

        /** @var LogExportQueueItemCollection $collection */
        $collection = $this->_logExportQueueItemCollectionFactory->create();
        $this->_collectionProcessor->process($searchCriteria, $collection);
        $collection->load();
        $searchResult = $this->_searchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @return SearchResultsInterface
     */
    public function getListUnexecuted(): SearchResultsInterface
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(
                'executed_at',
                null,
                'null'
            )
            ->create();

        return $this->getList($searchCriteria);
    }

    /**
     * @param LogExportQueueItem $logExportQueueItem
     * @return LogExportQueueItem
     * @throws CouldNotSaveException
     */
    public function save(LogExportQueueItem $logExportQueueItem) : LogExportQueueItem
    {
        try {
            $this->_logExportQueueItemResource->save($logExportQueueItem);
        } catch (Exception $e) {
            if ($logExportQueueItem->getId()) {
                throw new CouldNotSaveException(
                    __('Unable to save LogExportQueueItem entry with ID %1. Error: %2', [
                        $logExportQueueItem->getId(),
                        $e->getMessage()
                    ])
                );
            }

            throw new CouldNotSaveException(__('Unable to save new LogExportQueueItem entry. Error: %1', $e->getMessage()));
        }

        return $logExportQueueItem;
    }
}
