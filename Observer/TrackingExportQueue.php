<?php

namespace EffectConnect\Marketplaces\Observer;

use EffectConnect\Marketplaces\Objects\QueueHandlers\TrackingExportQueueHandler;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;

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
     * ShipmentExport constructor.
     * @param TrackingExportQueueHandler $trackingExportQueueHandler
     */
    public function __construct(
        TrackingExportQueueHandler $trackingExportQueueHandler
    ) {
        $this->_trackingExportQueueHandler = $trackingExportQueueHandler;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        // Get tracking information from event.
        /* @var ShipmentTrackInterface $shipmentTrack */
        $shipmentTrack = $observer->getEvent()->getTrack();

        // Add the tracking code to the queue (to be sent to EffectConnect by the cronjob).
        $this->_trackingExportQueueHandler->schedule($shipmentTrack->getEntityId());
    }
}
