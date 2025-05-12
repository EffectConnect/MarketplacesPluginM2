<?php

namespace EffectConnect\Marketplaces\Traits\Log;

use EffectConnect\Marketplaces\Enums\LogCode;
use EffectConnect\Marketplaces\Enums\LogSubjectType;
use EffectConnect\Marketplaces\Enums\LogType;
use EffectConnect\Marketplaces\Enums\Process;
use EffectConnect\Marketplaces\Model\OrderLine;
use EffectConnect\Marketplaces\Objects\Loggable;
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
     * @param int $shipmentId
     * @return bool
     */
    public function logExportShipmentSucceeded(int $connectionId, int $shipmentId) : bool
    {
        $loggable = new Loggable(
            LogType::SUCCESS(),
            LogCode::SHIPMENT_EXPORT_SUCCEEDED(),
            Process::EXPORT_ORDER_SHIPMENT(),
            $connectionId
        );

        $loggable->setMessage('Bulk shipment export to EffectConnect successful.');

        $loggable->setSubject(
            LogSubjectType::SHIPMENT(),
            $shipmentId
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param string $errorMessage
     * @param int $shipmentId
     * @return bool
     */
    public function logExportShipmentFailed(int $connectionId, string $errorMessage, int $shipmentId) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::SHIPMENT_EXPORT_FAILED(),
            Process::EXPORT_ORDER_SHIPMENT(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Bulk shipment export to EffectConnect failed with message [%s].',
            [
                $errorMessage
            ]
        );

        $loggable->setSubject(
            LogSubjectType::SHIPMENT(),
            $shipmentId
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
     * @param ShipmentInterface $shipment
     * @param string $errorMessage
     * @return bool
     */
    public function logExportShipmentOrderNotFoundError(ShipmentInterface $shipment, string $errorMessage) : bool
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
            $shipment->getEntityId()
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param string $errorMessage
     * @return bool
     */
    public function logExportShipmentConnectionError(int $connectionId, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::SHIPMENT_EXPORT_FAILED(),
            Process::EXPORT_ORDER_SHIPMENT(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Bulk shipment export to EffectConnect failed because of connection error. Message: [%s].',
            [
                $errorMessage
            ]
        );

        $loggable->setSubject(
            LogSubjectType::CONNECTION(),
            $connectionId
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

    /**
     * @param int $shipmentId
     * @param string $errorMessage
     * @return bool
     */
    public function logSaveShipmentFailedByShipmentId(int $shipmentId, string $errorMessage) : bool
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
            $shipmentId
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param ShipmentInterface $shipment
     * @param string $errorMessage
     * @return bool
     */
    public function logSaveShipmentSkipped(ShipmentInterface $shipment, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::WARNING(),
            LogCode::SHIPMENT_EXPORT_SKIPPED(),
            Process::EXPORT_ORDER_SHIPMENT(),
            null
        );

        $loggable->setFormattedMessage(
            'Shipment save to database skipped with message [%s].',
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

    /**
     * @param int $shipmentId
     * @param string $errorMessage
     * @return bool
     */
    public function logSaveShipmentSkippedByShipmentId(int $shipmentId, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::WARNING(),
            LogCode::SHIPMENT_EXPORT_SKIPPED(),
            Process::EXPORT_ORDER_SHIPMENT(),
            null
        );

        $loggable->setFormattedMessage(
            'Shipment save to database skipped with message [%s].',
            [
                $errorMessage
            ]
        );

        $loggable->setSubject(
            LogSubjectType::SHIPMENT(),
            $shipmentId
        );

        return $this->insertLogItem($loggable);
    }
}