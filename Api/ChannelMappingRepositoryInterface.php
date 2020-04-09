<?php

namespace EffectConnect\Marketplaces\Api;

use EffectConnect\Marketplaces\Model\ChannelMapping;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface ChannelMappingRepositoryInterface
 * @package EffectConnect\Marketplaces\Api
 */
interface ChannelMappingRepositoryInterface
{
    /**
     * @return ChannelMapping
     */
    public function create() : ChannelMapping;

    /**
     * @param ChannelMapping $channelMapping
     * @return ChannelMapping
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     */
    public function save(ChannelMapping $channelMapping) : ChannelMapping;

    /**
     * @param int $channelMappingId
     * @return ChannelMapping
     * @throws NoSuchEntityException
     */
    public function getById(int $channelMappingId) : ChannelMapping;

    /**
     * @param ChannelMapping $channelMapping
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ChannelMapping $channelMapping) : bool;

    /**
     * @param int $channelMappingId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $channelMappingId) : bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : SearchResultsInterface;

}
