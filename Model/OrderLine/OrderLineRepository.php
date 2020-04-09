<?php

namespace EffectConnect\Marketplaces\Model\OrderLine;

use EffectConnect\Marketplaces\Api\OrderLineRepositoryInterface;
use EffectConnect\Marketplaces\Model\OrderLine;
use EffectConnect\Marketplaces\Model\OrderLineFactory;
use EffectConnect\Marketplaces\Model\ResourceModel\OrderLine as OrderLineResource;
use EffectConnect\Marketplaces\Model\ResourceModel\OrderLine\Collection as OrderLineCollection;
use EffectConnect\Marketplaces\Model\ResourceModel\OrderLine\CollectionFactory as OrderLineCollectionFactory;
use Exception;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class OrderLineRepository
 * @package EffectConnect\Marketplaces\Model\OrderLine
 */
class OrderLineRepository implements OrderLineRepositoryInterface
{
    /**
     * @var OrderLineFactory
     */
    protected $_orderLineFactory;

    /**
     * @var OrderLineResource
     */
    protected $_orderLineResource;

    /**
     * @var OrderLineCollection
     */
    protected $_orderLineCollection;

    /**
     * @var OrderLineCollectionFactory
     */
    protected $_orderLineCollectionFactory;

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
     * OrderLineRepository constructor.
     * @param OrderLineFactory $orderLineFactory
     * @param OrderLineResource $orderLineResource
     * @param OrderLineCollection $orderLineCollection
     * @param OrderLineCollectionFactory $orderLineCollectionFactory
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        OrderLineFactory $orderLineFactory,
        OrderLineResource $orderLineResource,
        OrderLineCollection $orderLineCollection,
        OrderLineCollectionFactory $orderLineCollectionFactory,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->_orderLineFactory           = $orderLineFactory;
        $this->_orderLineResource          = $orderLineResource;
        $this->_orderLineCollection        = $orderLineCollection;
        $this->_orderLineCollectionFactory = $orderLineCollectionFactory;
        $this->_searchResultFactory        = $searchResultFactory;
        $this->_collectionProcessor        = $collectionProcessor;
        $this->_searchCriteriaBuilder      = $searchCriteriaBuilder;
    }

    /**
     * @return OrderLine
     */
    public function create() : OrderLine
    {
        return $this->_orderLineFactory->create();
    }

    /**
     * @param OrderLine $orderLine
     * @return OrderLine
     * @throws CouldNotSaveException
     */
    public function save(OrderLine $orderLine) : OrderLine
    {
        try {
            $this->_orderLineResource->save($orderLine);
        } catch (Exception $e) {
            if ($orderLine->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save order line with ID %1. Error: %2',
                        [$orderLine->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save order line. Error: %1', $e->getMessage()));
        }
        return $orderLine;
    }

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        if (is_null($searchCriteria)) {
            $searchCriteria = $this->_searchCriteriaBuilder->create();
        }

        $connectionStoreviewCollection = $this->_orderLineCollectionFactory->create();
        $this->_collectionProcessor->process($searchCriteria, $connectionStoreviewCollection);
        $this->_orderLineCollection->load();
        $searchResult = $this->_searchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($connectionStoreviewCollection->getItems());
        $searchResult->setTotalCount($connectionStoreviewCollection->getSize());

        return $searchResult;
    }

    /**
     * @param int $shipmentId
     * @return SearchResultsInterface
     */
    public function getListByShipmentId(int $shipmentId): SearchResultsInterface
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(
                'shipment_id',
                $shipmentId
            )
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * Get list of order lines for which the tracking number can be added to the queue.
     * This means that the track_id and the track_exported_at are both empty.
     * In case a tracking code is removed from Magento after export, then the track_id is empty and the
     * track_exported_at is not. We do not want to queue the item again.
     *
     * @param int $shipmentId
     * @return SearchResultsInterface
     */
    public function getListByShipmentIdForTrackQueue(int $shipmentId): SearchResultsInterface
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(
                'shipment_id',
                $shipmentId
            )
            ->addFilter(
                'track_id',
                '',
                'null'
            )
            ->addFilter(
                'track_exported_at',
                '',
                'null'
            )
            ->create();
        return $this->getList($searchCriteria);
    }


    /**
     * Get list of order lines for which the tracking code should be exported to EffectConnect.
     * This means that the track_id is not empty and the track_exported_at is.
     *
     * @return SearchResultsInterface
     */
    public function getItemForTrackExport(): SearchResultsInterface
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(
                'track_id',
                0,
                'gt'
            )
            ->addFilter(
                'track_exported_at',
                '',
                'null'
            )
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();
        return $this->getList($searchCriteria);
    }

    /**
     * @param int $quoteItemId
     * @return SearchResultsInterface
     */
    public function getListByQuoteItemId(int $quoteItemId) : SearchResultsInterface
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(
                'quote_item_id',
                $quoteItemId
            )
            ->create();
        return $this->getList($searchCriteria);
    }
}
