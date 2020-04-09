<?php

namespace EffectConnect\Marketplaces\Cron;

use EffectConnect\Marketplaces\Objects\QueueHandlers\LogExportQueueHandler;

/**
 * Class HandleLogExportQueue
 * @package EffectConnect\Marketplaces\Cron
 */
class HandleLogExportQueue
{
    /**
     * @var LogExportQueueHandler
     */
    protected $_logExportQueueHandler;

    /**
     * LogExportQueueHandler constructor.
     *
     * @param LogExportQueueHandler $logExportQueueHandler
     */
    public function __construct(LogExportQueueHandler $logExportQueueHandler)
    {
        $this->_logExportQueueHandler = $logExportQueueHandler;
    }

    /**
     * Executes when called by the cronjob.
     */
    public function execute()
    {
        $this->_logExportQueueHandler->execute();
    }
}