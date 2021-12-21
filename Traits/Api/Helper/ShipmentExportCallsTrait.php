<?php

namespace EffectConnect\Marketplaces\Traits\Api\Helper;

use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Model\OrderLine;
use EffectConnect\Marketplaces\Objects\ConnectionApi;
use EffectConnect\Marketplaces\Objects\TrackingExportDataObject;

/**
 * Trait ShipmentExportCallsTrait
 * @package EffectConnect\Marketplaces\Traits\Api\Helper
 */
trait ShipmentExportCallsTrait
{
    /**
     * @param ConnectionApi $connectionApi
     * @param TrackingExportDataObject $trackingExportDataObject
     * @return bool
     */
    protected function exportShipmentsProcedure(ConnectionApi $connectionApi, TrackingExportDataObject $trackingExportDataObject) : bool
    {
        $logHelper  = $connectionApi->getLogHelper();
        $apiWrapper = $connectionApi->getApiWrapper();
        $connection = $connectionApi->getConnection();
        $shipmentId = intval($trackingExportDataObject->getCurrentOrderLine() instanceof OrderLine ? $trackingExportDataObject->getCurrentOrderLine()->getShipmentId() : 0);

        try
        {
            // Send the tracking codes to EffectConnect.
            $apiWrapper->updateOrderLines($trackingExportDataObject);
            $logHelper->logExportShipmentSucceeded($connection->getEntityId(), $shipmentId);
        }
        catch (ApiCallFailedException $e)
        {
            $logHelper->logExportShipmentFailed($connection->getEntityId(), $e->getMessage(), $shipmentId);
            return false;
        }

        return true;
    }
}