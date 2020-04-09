<?php

namespace EffectConnect\Marketplaces\Api;

use EffectConnect\Marketplaces\Model\OrderLine;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Interface OrderLineRepositoryInterface
 * @package EffectConnect\Marketplaces\Api
 */
interface OrderLineRepositoryInterface
{
    /**
     * @return OrderLine
     */
    public function create();

    /**
     * @param OrderLine $orderLine
     * @return OrderLine
     * @throws CouldNotSaveException
     */
    public function save(OrderLine $orderLine);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : SearchResultsInterface;

    /**
     * @param int $shipmentId
     * @return SearchResultsInterface
     */
    public function getListByShipmentId(int $shipmentId) : SearchResultsInterface;

    /**
     * @param int $shipmentId
     * @return SearchResultsInterface
     */
    public function getListByShipmentIdForTrackQueue(int $shipmentId) : SearchResultsInterface;

    /**
     * @return SearchResultsInterface
     */
    public function getItemForTrackExport() : SearchResultsInterface;

    /**
     * @param int $quoteItemId
     * @return SearchResultsInterface
     */
    public function getListByQuoteItemId(int $quoteItemId) : SearchResultsInterface;
}
