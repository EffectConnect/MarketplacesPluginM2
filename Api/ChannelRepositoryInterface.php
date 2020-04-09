<?php

namespace EffectConnect\Marketplaces\Api;

use EffectConnect\Marketplaces\Model\Channel;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface ChannelRepositoryInterface
 * @package EffectConnect\Marketplaces\Api
 */
interface ChannelRepositoryInterface
{
    /**
     * @return Channel
     */
    public function create();

    /**
     * @param Channel $channel
     * @return Channel
     * @throws CouldNotSaveException
     */
    public function save(Channel $channel);

    /**
     * @param int $channelId
     * @return Channel
     * @throws NoSuchEntityException
     */
    public function getById(int $channelId);

    /**
     * @param Channel $channel
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Channel $channel);

    /**
     * @param int $channelId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $channelId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null) : SearchResultsInterface;

    /**
     * @param int $connectionId
     * @return SearchResultsInterface
     */
    public function getListByConnectionId(int $connectionId) : SearchResultsInterface;
}
