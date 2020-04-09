<?php

namespace EffectConnect\Marketplaces\Traits\Log;

use EffectConnect\Marketplaces\Enums\LogCode;
use EffectConnect\Marketplaces\Enums\LogSubjectType;
use EffectConnect\Marketplaces\Enums\LogType;
use EffectConnect\Marketplaces\Enums\Process;
use EffectConnect\Marketplaces\Model\OrderLine;
use EffectConnect\Marketplaces\Objects\Loggable;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;

/**
 * Trait ShipmentExportLoggablesTrait
 * @package EffectConnect\Marketplaces\Traits\Log
 */
trait ShipmentExportLoggablesTrait
{
    /**
     * @param int $connectionId
     * @param OrderInterface $order
     * @param ShipmentTrackInterface $shipmentTrack
     * @return bool
     */
    public function logExportShipmentSucceeded(int $connectionId, OrderInterface $order, ShipmentTrackInterface $shipmentTrack) : bool
    {
        $loggable = new Loggable(
            LogType::SUCCESS(),
            LogCode::SHIPMENT_EXPORT_SUCCEEDED(),
            Process::EXPORT_ORDER_SHIPMENT(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Shipment export to EffectConnect for order %s successful.',
            [
                $order->getIncrementId()
            ]
        );

        $loggable->setSubject(
            LogSubjectType::SHIPMENT(),
            $shipmentTrack->getShipment()->getEntityId()
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param OrderInterface $order
     * @param ShipmentTrackInterface $shipmentTrack
     * @param string $errorMessage
     * @return bool
     */
    public function logExportShipmentFailed(int $connectionId, OrderInterface $order, ShipmentTrackInterface $shipmentTrack, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::SHIPMENT_EXPORT_FAILED(),
            Process::EXPORT_ORDER_SHIPMENT(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Shipment export to EffectConnect for order %s failed with message [%s].',
            [
                $order->getIncrementId(),
                $errorMessage
            ]
        );

        $loggable->setSubject(
            LogSubjectType::SHIPMENT(),
            $shipmentTrack->getShipment()->getEntityId()
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param OrderLine $orderLine
     * @param string $errorMessage
     * @return bool
     */
    public function logExportShipmentOrderLineSaveError(OrderLine $orderLine, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::SHIPMENT_EXPORT_FAILED(),
            Process::EXPORT_ORDER_SHIPMENT(),
            null
        );

        $loggable->setFormattedMessage(
            'Shipment export to EffectConnect failed because order line could not be saved. Message: [%s].',
            [
                $errorMessage
            ]
        );

        $loggable->setPayload(json_encode([
            'orderLine' => $orderLine->getData()
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * @param ShipmentTrackInterface $shipmentTrack
     * @param string $errorMessage
     * @return bool
     */
    public function logExportShipmentOrderNotFoundError(ShipmentTrackInterface $shipmentTrack, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::SHIPMENT_EXPORT_FAILED(),
            Process::EXPORT_ORDER_SHIPMENT(),
            null
        );

        $loggable->setFormattedMessage(
            'Shipment export to EffectConnect failed because order was not found. Message: [%s].',
            [
                $errorMessage
            ]
        );

        $loggable->setSubject(
            LogSubjectType::SHIPMENT(),
            $shipmentTrack->getShipment()->getEntityId()
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param ShipmentTrackInterface $shipmentTrack
     * @param OrderInterface $order
     * @param string $errorMessage
     * @return bool
     */
    public function logExportShipmentConnectionError(ShipmentTrackInterface $shipmentTrack, OrderInterface $order, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::SHIPMENT_EXPORT_FAILED(),
            Process::EXPORT_ORDER_SHIPMENT(),
            null
        );

        $loggable->setFormattedMessage(
            'Shipment export for order %s to EffectConnect failed because of connection error. Message: [%s].',
            [
                $order->getIncrementId(),
                $errorMessage
            ]
        );

        $loggable->setSubject(
            LogSubjectType::SHIPMENT(),
            $shipmentTrack->getShipment()->getEntityId()
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param ShipmentTrackInterface $shipmentTrack
     * @param string $errorMessage
     * @return bool
     */
    public function logQueueShipmentGetOrderLinesError(ShipmentTrackInterface $shipmentTrack, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::SHIPMENT_EXPORT_FAILED(),
            Process::EXPORT_ORDER_SHIPMENT(),
            null
        );

        $loggable->setFormattedMessage(
            'Shipment export queue to EffectConnect failed when fetching order lines. Message: [%s].',
            [
                $errorMessage
            ]
        );

        $loggable->setSubject(
            LogSubjectType::SHIPMENT(),
            $shipmentTrack->getShipment()->getEntityId()
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param ShipmentInterface $shipment
     * @param string $errorMessage
     * @return bool
     */
    public function logSaveShipmentFailed(ShipmentInterface $shipment, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::SHIPMENT_EXPORT_FAILED(),
            Process::EXPORT_ORDER_SHIPMENT(),
            null
        );

        $loggable->setFormattedMessage(
            'Shipment save to database failed with message [%s].',
            [
                $errorMessage
            ]
        );

        $loggable->setSubject(
            LogSubjectType::SHIPMENT(),
            $shipment->getEntityId()
        );

        $loggable->setPayload(json_encode([
            'shipment' => $shipment->getData()
        ]));

        return $this->insertLogItem($loggable);
    }
}