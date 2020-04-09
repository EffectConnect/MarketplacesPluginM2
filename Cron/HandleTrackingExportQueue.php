<?php

namespace EffectConnect\Marketplaces\Cron;

use EffectConnect\Marketplaces\Objects\QueueHandlers\TrackingExportQueueHandler;

/**
 * Class HandleTrackingExportQueue
 * @package EffectConnect\Marketplaces\Cron
 */
class HandleTrackingExportQueue
{
    /**
     * @var TrackingExportQueueHandler
     */
    protected $_trackingExportQueueHandler;

    /**
     * TrackingExportQueueHandler constructor.
     *
     * @param TrackingExportQueueHandler $trackingExportQueueHandler
     */
    public function __construct(TrackingExportQueueHandler $trackingExportQueueHandler)
    {
        $this->_trackingExportQueueHandler = $trackingExportQueueHandler;
    }

    /**
     * Executes when called by the cronjob.
     */
    public function execute()
    {
        $this->_trackingExportQueueHandler->execute();
    }
}