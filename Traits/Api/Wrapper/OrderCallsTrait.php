<?php

namespace EffectConnect\Marketplaces\Traits\Api\Wrapper;

use DateTime;
use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Objects\ApiWrapper;
use EffectConnect\Marketplaces\Objects\TrackingExportDataObject;
use EffectConnect\PHPSdk\Core\CallType\OrderCall;
use EffectConnect\PHPSdk\Core\Interfaces\ResponseContainerInterface;
use EffectConnect\PHPSdk\Core\Model\Filter\FromDateFilter;
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
use Magento\Sales\Api\Data\ShipmentTrackInterface;

/**
 * Trait OrderCallsTrait
 * @package EffectConnect\Marketplaces\Traits\Api\Wrapper
 */
trait OrderCallsTrait
{
    /**
     * @param array $status
     * @param array $excludeTags
     * @param array $includeTags
     * @param DateTime|null $fromDate
     * @return OrderListReadResponseContainer
     * @throws ApiCallFailedException
     */
    public function orderListRead(array $status = [], array $excludeTags = [], array $includeTags = [], DateTime $fromDate = null) : OrderListReadResponseContainer
    {
        try
        {
            /** @var ApiWrapper $this */
            $core          = $this->getSdkCore();
            $orderListCall = $core->OrderListCall();
            $orderList     = new OrderList();

            if ($status) {
                $statusOpenFilter = new HasStatusFilter();
                $statusOpenFilter->setFilterValue($status);
                $orderList->addFilter($statusOpenFilter);
            }

            if ($excludeTags || $includeTags) {
                $tagsFilter = new HasTagFilter();
                foreach ($excludeTags as $excludeTag) {
                    $filterValue = new TagFilterValue();
                    $filterValue->setTagName($excludeTag);
                    $filterValue->setExclude(true);
                    $tagsFilter->setFilterValue($filterValue);
                }
                foreach ($includeTags as $includeTag) {
                    $filterValue = new TagFilterValue();
                    $filterValue->setTagName($includeTag);
                    $tagsFilter->setFilterValue($filterValue);
                }
                $orderList->addFilter($tagsFilter);
            }

            if ($fromDate instanceof DateTime) {
                $fromDateFilter = new FromDateFilter();
                $fromDateFilter->setFilterValue($fromDate);
                $orderList->addFilter($fromDateFilter);
            }
        }
        catch (Exception $e)
        {
            throw new ApiCallFailedException(__('Fetching orders from EffectConnect failed with message [%1].', $e->getMessage()));
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
        $carrier       = '';
        $trackingCode  = '';
        $ecOrderLineId = '';

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
                $ecOrderLineId = $ecOrderLine->getEcOrderLineId();

                $orderLinesToUpdate = (new OrderLineUpdate())
                    ->setOrderlineIdentifierType(OrderLineUpdate::TYPE_EFFECTCONNECT_LINE_ID)
                    ->setOrderlineIdentifier($ecOrderLineId);

                if ($shipmentTrack instanceof ShipmentTrackInterface) {
                    $trackingCode = $shipmentTrack->getTrackNumber();
                    $carrier      = $shipmentTrack->getCarrierCode();
                    $orderLinesToUpdate
                        ->setTrackingNumber($trackingCode)
                        ->setCarrier($carrier);
                }

                $orderUpdate->addLineUpdate($orderLinesToUpdate);

                $trackingExportDataObject->next();
            }
        }
        catch (Exception $e)
        {
            throw new ApiCallFailedException(__('Updating tracking info (carrier: %1, tracking code: %2) for EffectConnect order line ID %3 failed with message [%4].', $carrier, $trackingCode, $ecOrderLineId, $e->getMessage()));
        }

        $apiCall = $orderCall->update($orderUpdate);

        if (!is_null($this->_timeout)) {
            $apiCall->setTimeout($this->_timeout);
        }

        $apiCall->call();

        return $this->getResult($apiCall);
    }
}