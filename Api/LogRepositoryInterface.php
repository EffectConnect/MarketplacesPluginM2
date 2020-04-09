<?php

namespace EffectConnect\Marketplaces\Api;

use EffectConnect\Marketplaces\Model\Log;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface LogRepositoryInterface
 * @package EffectConnect\Marketplaces\Api
 */
interface LogRepositoryInterface
{
    /**
     * Lists Log entries that match specified search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria.
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : SearchResultsInterface;

    /**
     * Lists Log entries that match given connection ID.
     *
     * @param int $connectionId
     * @return SearchResultsInterface
     */
    public function getListByConnectionId(int $connectionId) : SearchResultsInterface;

    /**
     * Return Log object.
     *
     * @return Log
     */
    public function create() : Log;

    /**
     * Loads a specified Log object by it's ID.
     *
     * @param int $id
     * @return Log
     */
    public function getById(int $id) : Log;

    /**
     * Deletes a specified Log entry.
     *
     * @param Log $log
     * @return bool
     */
    public function delete(Log $log) : bool;

    /**
     * Deletes a specified Log entry by it's ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id) : bool;

    /**
     * Performs persist operations for a specified invoice.
     *
     * @param Log $log
     * @return Log
     */
    public function save(Log $log) : Log;
}
