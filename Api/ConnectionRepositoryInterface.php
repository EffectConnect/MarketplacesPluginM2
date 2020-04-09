<?php

namespace EffectConnect\Marketplaces\Api;

use EffectConnect\Marketplaces\Model\Connection;
use EffectConnect\Marketplaces\Model\ConnectionStoreview;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface ConnectionRepositoryInterface
 * @package EffectConnect\Marketplaces\Api
 */
interface ConnectionRepositoryInterface
{
    /**
     * @return Connection
     */
    public function create();

    /**
     * @param Connection $connection
     * @param ConnectionStoreview[] $connectionStoreviews
     * @return Connection
     * @throws CouldNotSaveException
     */
    public function save(Connection $connection, array $connectionStoreviews);

    /**
     * @param int $connectionId
     * @return Connection
     * @throws NoSuchEntityException
     */
    public function getById(int $connectionId);

    /**
     * @param Connection $connection
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Connection $connection);

    /**
     * @param int $connectionId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $connectionId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null) : SearchResultsInterface;
}
