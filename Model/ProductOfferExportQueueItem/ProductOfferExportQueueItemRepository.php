<?php

namespace EffectConnect\Marketplaces\Model\ProductOfferExportQueueItem;

use EffectConnect\Marketplaces\Api\ProductOfferExportQueueItemRepositoryInterface;
use EffectConnect\Marketplaces\Model\ProductOfferExportQueueItem;
use EffectConnect\Marketplaces\Model\ProductOfferExportQueueItemFactory;
use EffectConnect\Marketplaces\Model\ResourceModel\ProductOfferExportQueueItem as ProductOfferExportQueueItemResource;
use EffectConnect\Marketplaces\Model\ResourceModel\ProductOfferExportQueueItem\Collection as ProductOfferExportQueueItemCollection;
use EffectConnect\Marketplaces\Model\ResourceModel\ProductOfferExportQueueItem\CollectionFactory as ProductOfferExportQueueItemCollectionFactory;
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
 * Class ProductOfferExportQueueItemRepository
 * @package EffectConnect\Marketplaces\Model\ProductOfferExportQueueItem
 */
class ProductOfferExportQueueItemRepository implements ProductOfferExportQueueItemRepositoryInterface
{
    /**
     * @var ProductOfferExportQueueItem
     */
    protected $_productOfferExportQueueItemFactory;

    /**
     * @var ProductOfferExportQueueItemResource
     */
    protected $_productOfferExportQueueItemResource;

    /**
     * @var ProductOfferExportQueueItemCollection
     */
    protected $_productOfferExportQueueItemCollectionFactory;

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
     * ProductOfferExportQueueItemRepository constructor.
     *
     * @param ProductOfferExportQueueItemFactory $productOfferExportQueueItemFactory
     * @param ProductOfferExportQueueItemResource $productOfferExportQueueItemResource
     * @param ProductOfferExportQueueItemCollectionFactory $productOfferExportQueueItemCollectionFactory
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        ProductOfferExportQueueItemFactory $productOfferExportQueueItemFactory,
        ProductOfferExportQueueItemResource $productOfferExportQueueItemResource,
        ProductOfferExportQueueItemCollectionFactory $productOfferExportQueueItemCollectionFactory,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->_productOfferExportQueueItemFactory              = $productOfferExportQueueItemFactory;
        $this->_productOfferExportQueueItemResource             = $productOfferExportQueueItemResource;
        $this->_productOfferExportQueueItemCollectionFactory    = $productOfferExportQueueItemCollectionFactory;
        $this->_searchResultFactory                             = $searchResultFactory;
        $this->_collectionProcessor                             = $collectionProcessor;
        $this->_searchCriteriaBuilder                           = $searchCriteriaBuilder;
        $this->_sortOrderBuilder                                = $sortOrderBuilder;
    }

    /**
     * Load ProductOfferExportQueueItem entity by ID.
     *
     * @param int $id
     * @return ProductOfferExportQueueItem
     * @throws NoSuchEntityException
     */
    public function getById(int $id) : ProductOfferExportQueueItem
    {
        /** @var ProductOfferExportQueueItem $productOfferExportQueueItem */
        $productOfferExportQueueItem    = $this->_productOfferExportQueueItemFactory->create();

        $this->_productOfferExportQueueItemResource->load($productOfferExportQueueItem, $id);

        if (!$productOfferExportQueueItem->getId()) {
            throw new NoSuchEntityException(__('ProductOfferExportQueueItem entry with specified ID %1 not found.', $id));
        }

        return $productOfferExportQueueItem;
    }

    /**
     * Check if a non-executed queue item with a certain product id is in the queue table.
     *
     * @param int $productId
     * @return bool
     */
    public function isNonExecutedProductPresent(int $productId) : bool
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter('catalog_product_entity_id', $productId)
            ->addFilter('executed_at', null, 'null')
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        return $this->getList($searchCriteria)->getTotalCount() > 0;
    }

    /**
     * Create a new ProductOfferExportQueueItem entity.
     *
     * @return ProductOfferExportQueueItem
     */
    public function create() : ProductOfferExportQueueItem
    {
        return $this->_productOfferExportQueueItemFactory->create();
    }

    /**
     * Lists ProductOfferExportQueueItem entries that match specified search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : SearchResultsInterface
    {
        /** @var ProductOfferExportQueueItemCollection $collection */
        $collection     = $this->_productOfferExportQueueItemCollectionFactory->create();

        $this->_collectionProcessor->process($searchCriteria, $collection);

        $collection->load();

        $searchResult   = $this->_searchResultFactory->create();

        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Lists oldest ProductOfferExportQueueItem entry that is not executed yet.
     *
     * @param int $count
     * @return SearchResultsInterface
     */
    public function getOldestUnexecuted(int $count = 1) : SearchResultsInterface
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
            ->setPageSize($count)
            ->setCurrentPage(1)
            ->create();

        return $this->getList($searchCriteria);
    }

    /**
     * Delete a ProductOfferExportQueueItem entity.
     *
     * @param ProductOfferExportQueueItem $productOfferExportQueueItem
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ProductOfferExportQueueItem $productOfferExportQueueItem) : bool
    {
        try {
            $this->_productOfferExportQueueItemResource->delete($productOfferExportQueueItem);
        } catch (Exception $e) {
            if ($productOfferExportQueueItem->getId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove ProductOfferExportQueueItem entry with ID %1. Error: %2', [
                        $productOfferExportQueueItem->getId(),
                        $e->getMessage()
                    ])
                );
            }

            throw new CouldNotDeleteException(__('Unable to remove ProductOfferExportQueueItem entry. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * Delete a ProductOfferExportQueueItem entity by ID.
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $id) : bool
    {
        $productOfferExportQueueItemModel = $this->getById($id);
        $this->delete($productOfferExportQueueItemModel);

        return true;
    }

    /**
     * Save a ProductOfferExportQueueItem entity.
     *
     * @param ProductOfferExportQueueItem $productOfferExportQueueItem
     * @return ProductOfferExportQueueItem
     * @throws CouldNotSaveException
     */
    public function save(ProductOfferExportQueueItem $productOfferExportQueueItem) : ProductOfferExportQueueItem
    {
        try {
            $this->_productOfferExportQueueItemResource->save($productOfferExportQueueItem);
        } catch (Exception $e) {
            if ($productOfferExportQueueItem->getId()) {
                throw new CouldNotSaveException(
                    __('Unable to save ProductOfferExportQueueItem entry with ID %1. Error: %2', [
                        $productOfferExportQueueItem->getId(),
                        $e->getMessage()
                    ])
                );
            }

            throw new CouldNotSaveException(__('Unable to save new ProductOfferExportQueueItem entry. Error: %1', $e->getMessage()));
        }

        return $productOfferExportQueueItem;
    }
}
