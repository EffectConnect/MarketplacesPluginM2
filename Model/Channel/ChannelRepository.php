<?php

namespace EffectConnect\Marketplaces\Model\Channel;

use EffectConnect\Marketplaces\Api\ChannelRepositoryInterface;
use EffectConnect\Marketplaces\Model\ResourceModel\Channel as ChannelResource;
use EffectConnect\Marketplaces\Model\ResourceModel\Channel\CollectionFactory as ChannelCollectionFactory;
use EffectConnect\Marketplaces\Model\Channel;
use EffectConnect\Marketplaces\Model\ChannelFactory;
use Exception;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ChannelRepository
 * @package EffectConnect\Marketplaces\Model\Channel
 */
class ChannelRepository implements ChannelRepositoryInterface
{
    /**
     * @var ChannelFactory
     */
    protected $_channelFactory;

    /**
     * @var ChannelResource
     */
    protected $_channelResource;

    /**
     * @var ChannelCollectionFactory
     */
    protected $_channelCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var SearchResultFactory
     */
    protected $_searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $_collectionProcessor;

    /**
     * ChannelRepository constructor.
     * @param ChannelFactory $channelFactory
     * @param ChannelResource $channelResource
     * @param ChannelCollectionFactory $channelCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ChannelFactory $channelFactory,
        ChannelResource $channelResource,
        ChannelCollectionFactory $channelCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->_channelFactory        = $channelFactory;
        $this->_channelResource       = $channelResource;
        $this->_channelCollectionFactory     = $channelCollectionFactory;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_searchResultFactory   = $searchResultFactory;
        $this->_collectionProcessor   = $collectionProcessor;
    }

    /**
     * @return Channel
     */
    public function create() : Channel
    {
        $channel = $this->_channelFactory->create();
        return $channel;
    }

    /**
     * @param Channel $channel
     * @return Channel
     * @throws CouldNotSaveException
     */
    public function save(Channel $channel) : Channel
    {
        try {
            $this->_channelResource->save($channel);
        } catch (Exception $e) {
            if ($channel->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save channel with ID %1. Error: %2',
                        [$channel->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save channel. Error: %1', $e->getMessage()));
        }
        return $channel;
    }

    /**
     * @param int $channelId
     * @return Channel
     * @throws NoSuchEntityException
     */
    public function getById(int $channelId) : Channel
    {
        $channel = $this->create();
        $this->_channelResource->load($channel, $channelId);
        if (!$channel->getId()) {
            throw new NoSuchEntityException(__('Channel with specified ID %1 not found.', $channelId));
        }
        return $channel;
    }

    /**
     * @param int $channelId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $channelId) : bool
    {
        $channel = $this->getById($channelId);
        return $this->delete($channel);
    }

    /**
     * @param Channel $channel
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Channel $channel) : bool
    {
        try {
            $this->_channelResource->delete($channel);
        } catch (Exception $e) {
            if ($channel->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove channel with ID %1. Error: %2',
                        [$channel->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove channel. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null) : SearchResultsInterface
    {
        if (is_null($searchCriteria)) {
            $searchCriteria = $this->_searchCriteriaBuilder->create();
        }

        $channelCollection = $this->_channelCollectionFactory->create();
        $this->_collectionProcessor->process($searchCriteria, $channelCollection);
        $searchResult = $this->_searchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($channelCollection->getItems());
        $searchResult->setTotalCount($channelCollection->getSize());

        return $searchResult;
    }

    /**
     * @param int $connectionId
     * @return SearchResultsInterface
     */
    public function getListByConnectionId(int $connectionId) : SearchResultsInterface
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(
                'connection_id',
                $connectionId
            )
            ->create();
        return $this->getList($searchCriteria);
    }
}
