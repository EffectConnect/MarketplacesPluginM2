<?php

namespace EffectConnect\Marketplaces\Api;

use EffectConnect\Marketplaces\Model\ConnectionStoreview;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface ConnectionStoreviewRepositoryInterface
 * @package EffectConnect\Marketplaces\Api
 */
interface ConnectionStoreviewRepositoryInterface
{
    /**
     * @return ConnectionStoreview
     */
    public function create();

    /**
     * @param ConnectionStoreview $connectionStoreview
     * @return ConnectionStoreview
     * @throws CouldNotSaveException
     */
    public function save(ConnectionStoreview $connectionStoreview);

    /**
     * @param int $connectionStoreviewId
     * @return ConnectionStoreview
     * @throws NoSuchEntityException
     */
    public function getById(int $connectionStoreviewId);

    /**
     * @param ConnectionStoreview $connectionStoreview
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ConnectionStoreview $connectionStoreview);

    /**
     * @param int $connectionStoreviewId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $connectionStoreviewId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : SearchResultsInterface;

    /**
     * @param int $connectionId
     * @return SearchResultsInterface
     */
    public function getListByConnectionId(int $connectionId) : SearchResultsInterface;
}
