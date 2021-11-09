<?php

namespace EffectConnect\Marketplaces\Observer;

use EffectConnect\Marketplaces\Enums\ShipmentEvent as ShipmentEventEnum;
use EffectConnect\Marketplaces\Helper\SettingsHelper;
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
     * @var SettingsHelper
     */
    protected $_settingsHelper;

    /**
     * ShipmentExport constructor.
     * @param TrackingExportQueueHandler $trackingExportQueueHandler
     * @param SettingsHelper $settingsHelper
     */
    public function __construct(
        TrackingExportQueueHandler $trackingExportQueueHandler,
        SettingsHelper $settingsHelper
    ) {
        $this->_trackingExportQueueHandler = $trackingExportQueueHandler;
        $this->_settingsHelper = $settingsHelper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        // Only execute when shipment_export_settings/event setting is set to 'tracking' (default).
        if (strval($this->_settingsHelper->getShipmentExportEvent()) === strval(ShipmentEventEnum::TRACKING()))
        {
            // Get tracking information from event.
            /* @var ShipmentTrackInterface $shipmentTrack */
            $shipmentTrack = $observer->getEvent()->getTrack();

            // Add the tracking code to the queue (to be sent to EffectConnect by the cronjob).
            $this->_trackingExportQueueHandler->schedule($shipmentTrack->getEntityId());
        }
    }
}
