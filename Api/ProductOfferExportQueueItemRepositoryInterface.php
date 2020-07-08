<?php

namespace EffectConnect\Marketplaces\Api;

use EffectConnect\Marketplaces\Model\ProductOfferExportQueueItem;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ProductOfferExportQueueItemRepositoryInterface
 * @package EffectConnect\Marketplaces\Api
 */
interface ProductOfferExportQueueItemRepositoryInterface
{
    /**
     * Lists ProductOfferExportQueueItem entries that match specified search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria.
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : SearchResultsInterface;

    /**
     * Lists oldest ProductOfferExportQueueItem entry that is not executed yet.
     *
     * @param int $count
     * @return SearchResultsInterface
     */
    public function getOldestUnexecuted(int $count = 1) : SearchResultsInterface;

    /**
     * Return ProductOfferExportQueueItem object.
     *
     * @return ProductOfferExportQueueItem
     */
    public function create() : ProductOfferExportQueueItem;

    /**
     * Loads a specified ProductOfferExportQueueItem object by it's ID.
     *
     * @param int $id
     * @return ProductOfferExportQueueItem
     */
    public function getById(int $id) : ProductOfferExportQueueItem;

    /**
     * Check if a non-executed queue item with a certain product id is in the queue table.
     *
     * @param int $productId
     * @return bool
     */
    public function isNonExecutedProductPresent(int $productId) : bool;

    /**
     * Deletes a specified ProductOfferExportQueueItem entry.
     *
     * @param ProductOfferExportQueueItem $productOfferExportQueueItem
     * @return bool
     */
    public function delete(ProductOfferExportQueueItem $productOfferExportQueueItem) : bool;

    /**
     * Deletes a specified ProductOfferExportQueueItem entry by it's ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id) : bool;

    /**
     * Performs persist operations for a specified invoice.
     *
     * @param ProductOfferExportQueueItem $productOfferExportQueueItem
     * @return ProductOfferExportQueueItem
     */
    public function save(ProductOfferExportQueueItem $productOfferExportQueueItem) : ProductOfferExportQueueItem;
}
