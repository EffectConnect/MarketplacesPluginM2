<?php

namespace EffectConnect\Marketplaces\Observer;

use DateTime;
use EffectConnect\Marketplaces\Enums\ShipmentEvent as ShipmentEventEnum;
use EffectConnect\Marketplaces\Helper\LogHelper;
use EffectConnect\Marketplaces\Helper\SettingsHelper;
use EffectConnect\Marketplaces\Objects\QueueHandlers\TrackingExportQueueHandler;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class TrackingExportQueue
 * @package EffectConnect\Marketplaces\Observer
 */
class TrackingExportQueue implements ObserverInterface
{
    /**
     * @var TrackingExportQueueHandler
     */
    protected $_trackingExportQueueHandler;

    /**
     * @var SettingsHelper
     */
    protected $_settingsHelper;

    /**
     * @var LogHelper
     */
    protected $_logHelper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * ShipmentExport constructor.
     * @param TrackingExportQueueHandler $trackingExportQueueHandler
     * @param SettingsHelper $settingsHelper
     * @param LogHelper $_logHelper
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        TrackingExportQueueHandler $trackingExportQueueHandler,
        SettingsHelper $settingsHelper,
        LogHelper $_logHelper,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->_trackingExportQueueHandler = $trackingExportQueueHandler;
        $this->_settingsHelper = $settingsHelper;
        $this->_logHelper = $_logHelper;
        $this->_orderRepository = $orderRepository;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        // Get tracking information from event.
        /* @var ShipmentTrackInterface $shipmentTrack */
        $shipmentTrack = $observer->getEvent()->getTrack();

        try {
            // Only execute when shipment_export_settings/event setting is set to 'tracking' (default).
            if (strval($this->_settingsHelper->getShipmentExportEvent()) === strval(ShipmentEventEnum::TRACKING()))
            {
                // Orders older than 28 days can not be processed anymore by EC
                $orderId = intval($shipmentTrack->getOrderId());
                $order = $this->_orderRepository->get($orderId);
                $orderDate = new DateTime($order->getCreatedAt());
                if ($orderDate->diff(new DateTime())->days <= 28) {
                    // Add the tracking code to the queue (to be sent to EffectConnect by the cronjob).
                    $this->_trackingExportQueueHandler->schedule($shipmentTrack->getEntityId());
                } else {
                    try {
                        $shipment = $shipmentTrack->getShipment();
                        $this->_logHelper->logSaveShipmentSkipped($shipment, __('EffectConnect does not accept shipments for orders older than 30 days'));
                    } catch (LocalizedException $e) {
                        $this->_logHelper->logSaveShipmentSkippedByShipmentId($shipmentTrack->getParentId(), __('EffectConnect does not accept shipments for orders older than 30 days'));
                    }
                }
            }
        } catch (Exception $e) {
            $this->_logHelper->logSaveShipmentFailedByShipmentId($shipmentTrack->getParentId(), $e->getMessage());
        }
    }
}
