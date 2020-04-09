<?php

namespace EffectConnect\Marketplaces\Api;

use EffectConnect\Marketplaces\Model\DirectCatalogExportQueueItem;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface DirectCatalogExportQueueItemRepositoryInterface
 * @package EffectConnect\Marketplaces\Api
 */
interface DirectCatalogExportQueueItemRepositoryInterface
{
    /**
     * Lists DirectCatalogExportQueueItem entries that match specified search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria.
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : SearchResultsInterface;

    /**
     * Lists oldest DirectCatalogExportQueueItem entry that is not executed yet.
     *
     * @return SearchResultsInterface
     */
    public function getOldestUnexecuted() : SearchResultsInterface;

    /**
     * Return DirectCatalogExportQueueItem object.
     *
     * @return DirectCatalogExportQueueItem
     */
    public function create() : DirectCatalogExportQueueItem;

    /**
     * Loads a specified DirectCatalogExportQueueItem object by it's ID.
     *
     * @param int $id
     * @return DirectCatalogExportQueueItem
     */
    public function getById(int $id) : DirectCatalogExportQueueItem;

    /**
     * Check if a non-executed queue item with a certain connection id is in the queue table.
     *
     * @param int $connectionId
     * @return bool
     */
    public function isNonExecutedConnectionPresent(int $connectionId) : bool;

    /**
     * Deletes a specified DirectCatalogExportQueueItem entry.
     *
     * @param DirectCatalogExportQueueItem $directCatalogExportQueueItem
     * @return bool
     */
    public function delete(DirectCatalogExportQueueItem $directCatalogExportQueueItem) : bool;

    /**
     * Deletes a specified DirectCatalogExportQueueItem entry by it's ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id) : bool;

    /**
     * Performs persist operations for a specified invoice.
     *
     * @param DirectCatalogExportQueueItem $directCatalogExportQueueItem
     * @return DirectCatalogExportQueueItem
     */
    public function save(DirectCatalogExportQueueItem $directCatalogExportQueueItem) : DirectCatalogExportQueueItem;
}
