<?php

namespace EffectConnect\Marketplaces\Traits\Api\Wrapper;

use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Objects\ApiWrapper;
use EffectConnect\Marketplaces\Objects\TrackingExportDataObject;
use EffectConnect\PHPSdk\Core\CallType\OrderCall;
use EffectConnect\PHPSdk\Core\Interfaces\ResponseContainerInterface;
use EffectConnect\PHPSdk\Core\Model\Filter\HasStatusFilter;
use EffectConnect\PHPSdk\Core\Model\Filter\HasTagFilter;
use EffectConnect\PHPSdk\Core\Model\Filter\TagFilterValue;
use EffectConnect\PHPSdk\Core\Model\Request\OrderLineUpdate;
use EffectConnect\PHPSdk\Core\Model\Request\OrderList;
use EffectConnect\PHPSdk\Core\Model\Request\OrderReadRequest;
use EffectConnect\PHPSdk\Core\Model\Request\OrderUpdate;
use EffectConnect\PHPSdk\Core\Model\Request\OrderUpdateRequest;
use EffectConnect\PHPSdk\Core\Model\Response\OrderListReadResponseContainer;
use EffectConnect\PHPSdk\Core\Model\Response\OrderUpdateResponseContainer as OrderUpdated;
use Exception;

/**
 * Trait OrderCallsTrait
 * @package EffectConnect\Marketplaces\Traits\Api\Wrapper
 */
trait OrderCallsTrait
{
    /**
     * @param bool $onlyStatusPaid
     * @param array $excludeTags
     * @return OrderListReadResponseContainer
     * @throws ApiCallFailedException
     */
    public function orderListRead($onlyStatusPaid = false, $excludeTags = []) : OrderListReadResponseContainer
    {
        try
        {
            /** @var ApiWrapper $this */
            $core          = $this->getSdkCore();
            $orderListCall = $core->OrderListCall();
            $orderList     = new OrderList();

            if ($onlyStatusPaid) {
                $statusOpenFilter = new HasStatusFilter();
                $statusOpenFilter->setFilterValue([
                    HasStatusFilter::STATUS_PAID
                ]);
                $orderList->addFilter($statusOpenFilter);
            }

            if ($excludeTags) {
                $excludeTagsFilter = new HasTagFilter();
                foreach ($excludeTags as $excludeTag) {
                    $filterValue = new TagFilterValue();
                    $filterValue->setTagName($excludeTag);
                    $filterValue->setExclude(true);
                    $excludeTagsFilter->setFilterValue($filterValue);
                }
                $orderList->addFilter($excludeTagsFilter);
            }
        }
        catch (Exception $e)
        {
            throw new ApiCallFailedException(__('Fetching open orders from EffectConnect failed with message [%1].', $e->getMessage()));
        }

        $apiCall = $orderListCall->read($orderList);

        if (!is_null($this->_timeout)) {
            $apiCall->setTimeout($this->_timeout);
        }

        $apiCall->call();

        return $this->getResult($apiCall);
    }

    /**
     * @param string $effectConnectNumber
     * @return ResponseContainerInterface
     * @throws ApiCallFailedException
     */
    public function orderRead(string $effectConnectNumber)
    {
        try
        {
            /* @var OrderCall $orderCall */
            $core      = $this->getSdkCore();
            $orderCall = $core->OrderCall();

            $orderData = (new OrderReadRequest())
                ->setIdentifierType(OrderReadRequest::TYPE_EFFECTCONNECT_NUMBER)
                ->setIdentifier($effectConnectNumber);
        }
        catch (Exception $e)
        {
            throw new ApiCallFailedException(__('Fetching order %1 from EffectConnect failed with message [%2].', $effectConnectNumber, $e->getMessage()));
        }

        $apiCall = $orderCall->read($orderData);

        if (!is_null($this->_timeout)) {
            $apiCall->setTimeout($this->_timeout);
        }

        $apiCall->call();

        return $this->getResult($apiCall);
    }

    /**
     * @param string $effectConnectNumber
     * @param string $magentoOrderId
     * @param string $magentoOrderNumber
     * @return OrderUpdated
     * @throws ApiCallFailedException
     */
    public function updateOrder(string $effectConnectNumber, string $magentoOrderId, string $magentoOrderNumber) : OrderUpdated
    {
        try
        {
            /* @var OrderCall $orderCall */
            $core      = $this->getSdkCore();
            $orderCall = $core->OrderCall();

            $orderData = new OrderUpdate();
            $orderData
                ->setOrderIdentifierType(OrderUpdate::TYPE_EFFECTCONNECT_NUMBER)
                ->setOrderIdentifier($effectConnectNumber)
                ->setConnectionIdentifier($magentoOrderId)
                ->setConnectionNumber($magentoOrderNumber);

            $orderUpdate = new OrderUpdateRequest();
            $orderUpdate->addOrderUpdate($orderData);
        }
        catch (Exception $e)
        {
            throw new ApiCallFailedException(__('Updating order with Magento ID %1 to EffectConnect failed with messages [%2].', $magentoOrderId, $e->getMessage()));
        }

        $apiCall = $orderCall->update($orderUpdate);

        if (!is_null($this->_timeout)) {
            $apiCall->setTimeout($this->_timeout);
        }

        $apiCall->call();

        return $this->getResult($apiCall);
    }

    /**
     * @param string $effectConnectNumber
     * @param string $tag
     * @return ResponseContainerInterface
     * @throws ApiCallFailedException
     */
    public function updateOrderAddTag(string $effectConnectNumber, string $tag)
    {
        try
        {
            /* @var OrderCall $orderCall */
            $core      = $this->getSdkCore();
            $orderCall = $core->OrderCall();

            $orderAddTag = new OrderUpdate();
            $orderAddTag
                ->setOrderIdentifierType(OrderUpdate::TYPE_EFFECTCONNECT_NUMBER)
                ->setOrderIdentifier($effectConnectNumber)
                ->addTag($tag);

            $orderUpdate = new OrderUpdateRequest();
            $orderUpdate->addOrderUpdate($orderAddTag);
        }
        catch (Exception $e)
        {
            throw new ApiCallFailedException(__('Adding tags to EffectConnect order %1 failed with messages [%2].', $effectConnectNumber, $e->getMessage()));
        }

        $apiCall = $orderCall->update($orderUpdate);

        if (!is_null($this->_timeout)) {
            $apiCall->setTimeout($this->_timeout);
        }

        $apiCall->call();

        return $this->getResult($apiCall);
    }

    /**
     * @param TrackingExportDataObject $trackingExportDataObject
     * @return OrderUpdated
     * @throws ApiCallFailedException
     */
    public function updateOrderLines(TrackingExportDataObject $trackingExportDataObject) : OrderUpdated
    {
        try
        {
            /* @var OrderCall $orderCall */
            $core        = $this->getSdkCore();
            $orderCall   = $core->OrderCall();
            $orderUpdate = new OrderUpdateRequest();

            // For each order line update the track and trace data.
            while ($trackingExportDataObject->valid())
            {
                $shipmentTrack = $trackingExportDataObject->getCurrentShipmentTrack();
                $ecOrderLine   = $trackingExportDataObject->getCurrentOrderLine();

                $orderlinesToUpdate = (new OrderLineUpdate())
                    ->setOrderlineIdentifierType(OrderLineUpdate::TYPE_EFFECTCONNECT_LINE_ID)
                    ->setOrderlineIdentifier($ecOrderLine->getEcOrderLineId())
                    ->setTrackingNumber($shipmentTrack->getTrackNumber())
                    ->setCarrier($shipmentTrack->getCarrierCode());
                $orderUpdate->addLineUpdate($orderlinesToUpdate);

                $trackingExportDataObject->next();
            }
        }
        catch (Exception $e)
        {
            throw new ApiCallFailedException(__('Updating tracking info (carrier: %1, tracking code: %2) for EffectConnect order %3 failed with message [%4].', $carrier, $trackingCode, $effectConnectNumber, $e->getMessage()));
        }

        $apiCall = $orderCall->update($orderUpdate);

        if (!is_null($this->_timeout)) {
            $apiCall->setTimeout($this->_timeout);
        }

        $apiCall->call();

        return $this->getResult($apiCall);
    }
}