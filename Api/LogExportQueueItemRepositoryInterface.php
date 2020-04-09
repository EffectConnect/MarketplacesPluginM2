<?php

namespace EffectConnect\Marketplaces\Api;

use EffectConnect\Marketplaces\Model\LogExportQueueItem;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Interface LogExportQueueItemRepositoryInterface
 * @package EffectConnect\Marketplaces\Api
 */
interface LogExportQueueItemRepositoryInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria.
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null) : SearchResultsInterface;

    /**
     * @return SearchResultsInterface
     */
    public function getListUnexecuted() : SearchResultsInterface;

    /**
     * @return LogExportQueueItem
     */
    public function create() : LogExportQueueItem;

    /**
     * @param int $connectionId
     * @return bool
     */
    public function isNonExecutedConnectionPresent(int $connectionId) : bool;

    /**
     * @param LogExportQueueItem $logExportQueueItem
     * @return LogExportQueueItem
     * @throws CouldNotSaveException
     */
    public function save(LogExportQueueItem $logExportQueueItem) : LogExportQueueItem;
}
