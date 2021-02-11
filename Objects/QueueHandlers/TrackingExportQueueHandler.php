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
     * @param ConnectionRepositoryInterface $connectionRepository
     */
    public function __construct(
        ApiHelper $apiHelper,
        LogHelper $logHelper,
        SettingsHelper $settingsHelper,
        OrderRepositoryInterface $orderRepositoryInterface,
        OrderLineRepositoryInterface $orderLineRepository,
        ShipmentTrackRepositoryInterface $shipmentTrackRepository,
        ConnectionRepositoryInterface $connectionRepository
    ) {
        $this->_apiHelper               = $apiHelper;
        $this->_logHelper               = $logHelper;
        $this->_settingsHelper          = $settingsHelper;
        $this->_orderRepository         = $orderRepositoryInterface;
        $this->_orderLineRepository     = $orderLineRepository;
        $this->_shipmentTrackRepository = $shipmentTrackRepository;
        $this->_connectionRepository    =$connectionRepository;
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

            // Save the tracking ID to each orderline within the shipment.
            foreach ($ecOrderLines as $ecOrderLine)
            {
                $ecOrderLine->setTrackId($shipmentTrack->getEntityId());
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
                $shipmentTrack = $this->_shipmentTrackRepository->get($orderLineToExport->getTrackId());
                if ($shipmentTrack->getEntityId() > 0)
                {
                    try {
                        // Get order from tracking info.
                        $orderId = $shipmentTrack->getOrderId();
                        $order = $this->_orderRepository->get($orderId);
                    } catch (Exception $e) {
                        $this->_logHelper->logExportShipmentOrderNotFoundError($shipmentTrack, $e->getMessage());
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
                    // Bad luck if the export fails, we will not try to do this again.
                    $orderLineToExport->setTrackExportedAt(date('Y-m-d H:i:s', time()));
                    try {
                        $this->_orderLineRepository->save($orderLineToExport);
                    } catch (CouldNotSaveException $e) {
                        $this->_logHelper->logExportShipmentOrderLineSaveError($orderLineToExport, $e->getMessage());
                        continue;
                    }

                    $orderLinesToExportByConnection[$connectionId]->addTrackingExportData(
                        $shipmentTrack,
                        $orderLineToExport
                    );
                }
            }
        }

        // API call for each conenction
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
                continue;
            }

            // Export the shipments info to EffectConnect.
            $connectionApi->exportShipments($trackingExportDataObject);
        }
    }
}