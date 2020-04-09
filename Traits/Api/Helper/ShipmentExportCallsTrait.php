<?php

namespace EffectConnect\Marketplaces\Traits\Api\Helper;

use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Model\OrderLine;
use EffectConnect\Marketplaces\Objects\ConnectionApi;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;

/**
 * Trait ShipmentExportCallsTrait
 * @package EffectConnect\Marketplaces\Traits\Api\Helper
 */
trait ShipmentExportCallsTrait
{
    /**
     * @param ConnectionApi $connectionApi
     * @param OrderInterface $order
     * @param ShipmentTrackInterface $shipmentTrack
     * @param OrderLine $ecOrderLine
     * @return bool
     */
    protected function exportShipmentProcedure(ConnectionApi $connectionApi, OrderInterface $order, ShipmentTrackInterface $shipmentTrack, OrderLine $ecOrderLine) : bool
    {
        $logHelper  = $connectionApi->getLogHelper();
        $apiWrapper = $connectionApi->getApiWrapper();
        $connection = $connectionApi->getConnection();

        try
        {
            // Extract ID from the EC order line.
            $ecOrderLineIdentifiers = [$ecOrderLine->getEcOrderLineId()];

            // Send the tracking code to EffectConnect.
            $apiWrapper->updateOrderLines($order->getEcMarketplacesIdentificationNumber(), $shipmentTrack->getCarrierCode(), $shipmentTrack->getTrackNumber(), $ecOrderLineIdentifiers);
            $logHelper->logExportShipmentSucceeded($connection->getEntityId(), $order, $shipmentTrack);
        }
        catch (ApiCallFailedException $e)
        {
            $logHelper->logExportShipmentFailed($connection->getEntityId(), $order, $shipmentTrack, $e->getMessage());
            return false;
        }

        return true;
    }
}