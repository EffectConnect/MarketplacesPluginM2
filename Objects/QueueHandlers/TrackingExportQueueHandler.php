<?php

namespace EffectConnect\Marketplaces\Objects\QueueHandlers;

use EffectConnect\Marketplaces\Api\ConnectionRepositoryInterface;
use EffectConnect\Marketplaces\Api\OrderLineRepositoryInterface;
use EffectConnect\Marketplaces\Helper\ApiHelper;
use EffectConnect\Marketplaces\Helper\LogHelper;
use EffectConnect\Marketplaces\Helper\SettingsHelper;
use EffectConnect\Marketplaces\Interfaces\QueueHandlerInterface;
use EffectConnect\Marketplaces\Objects\TrackingExportDataObject;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;

/**
 * Class TrackingExportQueueHandler
 * @package EffectConnect\Marketplaces\Objects\QueueHandlers
 */
class TrackingExportQueueHandler implements QueueHandlerInterface
{
    /**
     * @var ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var OrderLineRepositoryInterface
     */
    protected $_orderLineRepository;

    /**
     * @var LogHelper
     */
    protected $_logHelper;

    /**
     * @var SettingsHelper
     */
    protected $_settingsHelper;

    /**
     * @var ShipmentTrackRepositoryInterface
     */
    protected $_shipmentTrackRepository;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $_shipmentRepository;

    /**
     * @var ConnectionRepositoryInterface
     */
    protected $_connectionRepository;

    /**
     * TrackingExportQueueHandler constructor.
     *
     * @param ApiHelper $apiHelper
     * @param LogHelper $logHelper
     * @param SettingsHelper $settingsHelper
     * @param OrderRepositoryInterface $orderRepositoryInterface
     * @param OrderLineRepositoryInterface $orderLineRepository
     * @param ShipmentTrackRepositoryInterface $shipmentTrackRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ConnectionRepositoryInterface $connectionRepository
     */
    public function __construct(
        ApiHelper $apiHelper,
        LogHelper $logHelper,
        SettingsHelper $settingsHelper,
        OrderRepositoryInterface $orderRepositoryInterface,
        OrderLineRepositoryInterface $orderLineRepository,
        ShipmentTrackRepositoryInterface $shipmentTrackRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        ConnectionRepositoryInterface $connectionRepository
    ) {
        $this->_apiHelper               = $apiHelper;
        $this->_logHelper               = $logHelper;
        $this->_settingsHelper          = $settingsHelper;
        $this->_orderRepository         = $orderRepositoryInterface;
        $this->_orderLineRepository     = $orderLineRepository;
        $this->_shipmentTrackRepository = $shipmentTrackRepository;
        $this->_shipmentRepository      = $shipmentRepository;
        $this->_connectionRepository    = $connectionRepository;
    }

    /**
     * @param int $id
     * @return void
     */
    public function schedule(int $id)
    {
        // Save tracking ID in case it has a shipment that is linked to a EffectConnect order.
        $shipmentTrack = $this->_shipmentTrackRepository->get($id);
        if ($shipmentTrack->getEntityId() > 0 )
        {
            try {
                // Get EC order lines that are linked to current shipment.
                $shipmentId = $shipmentTrack->getShipment()->getEntityId();
                $ecOrderLines = $this->_orderLineRepository->getListByShipmentIdForTrackQueue($shipmentId)->getItems();
            } catch (Exception $e) {
                $this->_logHelper->logQueueShipmentGetOrderLinesError($shipmentTrack, $e->getMessage());
                return;
            }

            // Save the tracking ID to each order line within the shipment.
            foreach ($ecOrderLines as $ecOrderLine)
            {
                $ecOrderLine->setTrackId($shipmentTrack->getEntityId());
                $ecOrderLine->setExport(1);

                try {
                    $this->_orderLineRepository->save($ecOrderLine);
                } catch (CouldNotSaveException $e) {
                    $this->_logHelper->logQueueShipmentGetOrderLinesError($shipmentTrack, $e->getMessage());
                    return;
                }
            }
        }
    }

    /**
     * @return void
     */
    public function execute()
    {
        $orderLinesToExport             = $this->_orderLineRepository->getItemForTrackExport();
        $queueSize                      = intval($this->_settingsHelper->getShipmentExportQueueSize() ?? static::DEFAULT_MAX_QUEUE_ITEMS_PER_EXECUTE);
        $orderLinesToExportByConnection = [];

        // Group shipments by connection
        if ($orderLinesToExport->getTotalCount() > 0)
        {
            foreach ($orderLinesToExport->getItems() as $orderLineToExport)
            {
                // Load shipment
                $shipment = $this->_shipmentRepository->get($orderLineToExport->getShipmentId());

                // Load shipment tracking in case order line has one
                $shipmentTrack = $this->_shipmentTrackRepository->get($orderLineToExport->getTrackId());
                if (intval($shipmentTrack->getEntityId()) === 0) {
                    $shipmentTrack = null;
                }

                try {
                    // Get order from tracking info.
                    $orderId = $shipment->getOrderId();
                    $order = $this->_orderRepository->get($orderId);
                } catch (Exception $e) {
                    $this->_logHelper->logExportShipmentOrderNotFoundError($shipment, $e->getMessage());
                    continue;
                }

                $connectionId = intval($order->getEcMarketplacesConnectionId());

                if (!isset($orderLinesToExportByConnection[$connectionId])) {
                    $orderLinesToExportByConnection[$connectionId] = new TrackingExportDataObject();
                }

                // Take config setting for queue size into account
                if (count($orderLinesToExportByConnection[$connectionId]) >= $queueSize) {
                    continue;
                }

                // Save that we are exporting this tracking code to prevent other cronjobs to process the same item.
                $orderLineToExport->setTrackExportStartedAt(date('Y-m-d H:i:s', time()));
                try {
                    $this->_orderLineRepository->save($orderLineToExport);
                } catch (CouldNotSaveException $e) {
                    $this->_logHelper->logExportShipmentOrderLineSaveError($orderLineToExport, $e->getMessage());
                    continue;
                }

                $orderLinesToExportByConnection[$connectionId]->addTrackingExportData(
                    $orderLineToExport,
                    $shipmentTrack
                );
            }
        }

        // API call for each connection
        foreach ($orderLinesToExportByConnection as $connectionId => $trackingExportDataObject)
        {
            // Only export to existing and active connections
            try {
                $connection = $this->_connectionRepository->getById($connectionId);
            } catch (NoSuchEntityException $e) {
                $this->_logHelper->logExportShipmentConnectionError($connectionId, $e->getMessage());
                continue;
            }

            if (intval($connection->getIsActive()) === 0) {
                continue;
            }

            try {
                // Get the connection to export the tracking info to.
                $connectionApi = $this->_apiHelper->getConnectionApi($connectionId);
            } catch (Exception $e) {
                $this->_logHelper->logExportShipmentConnectionError($connectionId, $e->getMessage());
                $this->finishExport($trackingExportDataObject, false);
                continue;
            }

            // Export the shipments info to EffectConnect.
            $result = $connectionApi->exportShipments($trackingExportDataObject);
            $this->finishExport($trackingExportDataObject, $result);
        }
    }

    /**
     * @param TrackingExportDataObject $trackingExportDataObject
     * @param bool $result
     * @return void
     */
    protected function finishExport(TrackingExportDataObject $trackingExportDataObject, bool $result)
    {
        // For each order line update the result.
        $trackingExportDataObject->rewind();
        while ($trackingExportDataObject->valid())
        {
            $orderLineToExport = $trackingExportDataObject->getCurrentOrderLine();
            if ($result) {
                // Save that we have exported the tracking codes to make sure they are not exported again.
                $orderLineToExport->setTrackExportedAt(date('Y-m-d H:i:s', time()));
            } else {
                // Reset that we have started to export the tracking codes to make sure there are exported in the next run.
                $orderLineToExport->setTrackExportStartedAt(null);
            }

            try {
                $this->_orderLineRepository->save($orderLineToExport);
            } catch (CouldNotSaveException $e) {
                $this->_logHelper->logExportShipmentOrderLineSaveError($orderLineToExport, $e->getMessage());
                continue;
            }

            $trackingExportDataObject->next();
        }
    }
}