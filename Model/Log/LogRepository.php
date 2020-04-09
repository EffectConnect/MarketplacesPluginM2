<?php

namespace EffectConnect\Marketplaces\Model\Log;

use EffectConnect\Marketplaces\Api\LogRepositoryInterface;
use EffectConnect\Marketplaces\Model\Log;
use EffectConnect\Marketplaces\Model\LogFactory;
use EffectConnect\Marketplaces\Model\ResourceModel\Log as LogResource;
use EffectConnect\Marketplaces\Model\ResourceModel\Log\Collection as LogCollection;
use EffectConnect\Marketplaces\Model\ResourceModel\Log\CollectionFactory as LogCollectionFactory;
use Exception;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class LogRepository
 * @package EffectConnect\Marketplaces\Model\Log
 */
class LogRepository implements LogRepositoryInterface
{
    /**
     * @var Log
     */
    protected $_logFactory;

    /**
     * @var LogResource
     */
    protected $_logResource;

    /**
     * @var LogCollection
     */
    protected $_logCollectionFactory;

    /**
     * @var SearchResultFactory
     */
    protected $_searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $_collectionProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * LogRepository constructor.
     *
     * @param LogFactory $logFactory
     * @param LogResource $logResource
     * @param LogCollectionFactory $logCollectionFactory
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        LogFactory $logFactory,
        LogResource $logResource,
        LogCollectionFactory $logCollectionFactory,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->_logFactory              = $logFactory;
        $this->_logResource             = $logResource;
        $this->_logCollectionFactory    = $logCollectionFactory;
        $this->_searchResultFactory     = $searchResultFactory;
        $this->_collectionProcessor     = $collectionProcessor;
        $this->_searchCriteriaBuilder   = $searchCriteriaBuilder;
    }

    /**
     * Load log entity by ID.
     *
     * @param int $id
     * @return Log
     * @throws NoSuchEntityException
     */
    public function getById(int $id) : Log
    {
        /** @var Log $log */
        $log    = $this->_logFactory->create();

        $this->_logResource->load($log, $id);

        if (!$log->getId()) {
            throw new NoSuchEntityException(__('Log entry with specified ID %1 not found.', $id));
        }

        return $log;
    }

    /**
     * Create a new Log entity.
     *
     * @return Log
     */
    public function create() : Log
    {
        return $this->_logFactory->create();
    }

    /**
     * Find log entities by criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : SearchResultsInterface
    {
        /** @var LogCollection $collection */
        $collection     = $this->_logCollectionFactory->create();

        $this->_collectionProcessor->process($searchCriteria, $collection);

        $collection->load();

        $searchResult   = $this->_searchResultFactory->create();

        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

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

    /**
     * Delete a log entity.
     *
     * @param Log $log
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Log $log) : bool
    {
        try {
            $this->_logResource->delete($log);
        } catch (Exception $e) {
            if ($log->getId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove log entry with ID %1. Error: %2', [
                        $log->getId(),
                        $e->getMessage()
                    ])
                );
            }

            throw new CouldNotDeleteException(__('Unable to remove log entry. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * Delete a log entity by ID.
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $id) : bool
    {
        $logModel = $this->getById($id);
        $this->delete($logModel);

        return true;
    }

    /**
     * Save a log entity.
     *
     * @param Log $log
     * @return Log
     * @throws CouldNotSaveException
     */
    public function save(Log $log) : Log
    {
        try {
            $this->_logResource->save($log);
        } catch (Exception $e) {
            if ($log->getId()) {
                throw new CouldNotSaveException(
                    __('Unable to save log entry with ID %1. Error: %2', [
                        $log->getId(),
                        $e->getMessage()
                    ])
                );
            }

            throw new CouldNotSaveException(__('Unable to save new log entry. Error: %1', $e->getMessage()));
        }

        return $log;
    }
}
