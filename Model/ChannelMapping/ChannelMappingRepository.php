<?php

namespace EffectConnect\Marketplaces\Model\ChannelMapping;

use EffectConnect\Marketplaces\Api\ChannelMappingRepositoryInterface;
use EffectConnect\Marketplaces\Model\ChannelMapping;
use EffectConnect\Marketplaces\Model\ChannelMappingFactory;
use EffectConnect\Marketplaces\Model\ResourceModel\ChannelMapping as ChannelMappingResource;
use EffectConnect\Marketplaces\Model\ResourceModel\ChannelMapping\Collection as ChannelMappingCollection;
use Exception;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ChannelMappingRepository
 * @package EffectConnect\Marketplaces\Model\ChannelMapping
 */
class ChannelMappingRepository implements ChannelMappingRepositoryInterface
{
    /**
     * @var ChannelMappingFactory
     */
    protected $_channelMappingFactory;

    /**
     * @var ChannelMappingResource
     */
    protected $_channelMappingResource;

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
     * @var ChannelMappingCollection
     */
    protected $_channelMappingCollection;

    /**
     * ChannelMappingRepository constructor.
     * @param ChannelMappingCollection $channelMappingCollection
     * @param ChannelMappingFactory $channelMappingFactory
     * @param ChannelMappingResource $channelMappingResource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ChannelMappingCollection $channelMappingCollection,
        ChannelMappingFactory $channelMappingFactory,
        ChannelMappingResource $channelMappingResource,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->_channelMappingFactory    = $channelMappingFactory;
        $this->_channelMappingResource   = $channelMappingResource;
        $this->_channelMappingCollection = $channelMappingCollection;
        $this->_searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->_searchResultFactory      = $searchResultFactory;
        $this->_collectionProcessor      = $collectionProcessor;
    }

    /**
     * @return ChannelMapping
     */
    public function create() : ChannelMapping
    {
        $connection = $this->_channelMappingFactory->create();
        return $connection;
    }

    /**
     * @param int $channelMappingId
     * @return ChannelMapping
     * @throws NoSuchEntityException
     */
    public function getById(int $channelMappingId) : ChannelMapping
    {
        $channelMapping = $this->create();
        $this->_channelMappingResource->load($channelMapping, $channelMappingId);
        if (!$channelMapping->getId()) {
            throw new NoSuchEntityException(__('Channel mapping with specified ID %1 not found.', $channelMappingId));
        }
        return $channelMapping;
    }

    /**
     * @param int $channelMappingId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $channelMappingId) : bool
    {
        $model = $this->getById($channelMappingId);
        $this->delete($model);
        return true;
    }

    /**
     * @param ChannelMapping $channelMapping
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ChannelMapping $channelMapping) : bool
    {
        try {
            $this->_channelMappingResource->delete($channelMapping);
        } catch (Exception $e) {
            if ($channelMapping->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove channel mapping with ID %1. Error: %2',
                        [$channelMapping->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove channel mapping. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * @param ChannelMapping $channelMapping
     * @return ChannelMapping
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     */
    public function save(ChannelMapping $channelMapping) : ChannelMapping
    {
        try {
            $this->_channelMappingResource->save($channelMapping);
        } catch (AlreadyExistsException $e) {
            if ($channelMapping->getId()) {
                throw new AlreadyExistsException(
                    __(
                        'Unable to save channel mapping with ID %1, because it already exists. Error: %2',
                        [$channelMapping->getId(), $e->getMessage()]
                    )
                );
            }
            throw new AlreadyExistsException(__('Unable to save channel mapping, because it already exists. Error: %1', $e->getMessage()));
        } catch (Exception $e) {
            if ($channelMapping->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save channel mapping with ID %1. Error: %2',
                        [$channelMapping->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save channel mapping. Error: %1', $e->getMessage()));
        }
        return $channelMapping;
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

        $this->_collectionProcessor->process($searchCriteria, $this->_channelMappingCollection);
        $this->_channelMappingCollection->load();
        $searchResult = $this->_searchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($this->_channelMappingCollection->getItems());
        $searchResult->setTotalCount($this->_channelMappingCollection->getSize());

        return $searchResult;
    }
}
