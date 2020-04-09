<?php

namespace EffectConnect\Marketplaces\Cron;

use EffectConnect\Marketplaces\Objects\QueueHandlers\DirectCatalogExportQueueHandler;

/**
 * Class HandleDirectCatalogExportQueue
 * @package EffectConnect\Marketplaces\Cron
 */
class HandleDirectCatalogExportQueue {
    /**
     * @var DirectCatalogExportQueueHandler
     */
    protected $_directCatalogExportQueueHandler;

    /**
     * HandleDirectCatalogExportQueue constructor.
     *
     * @param DirectCatalogExportQueueHandler $directCatalogExportQueueHandler
     */
    public function __construct(DirectCatalogExportQueueHandler $directCatalogExportQueueHandler) {
        $this->_directCatalogExportQueueHandler = $directCatalogExportQueueHandler;
    }

    /**
     * Executes when called by the cronjob.
     */
    public function execute()
    {
        $this->_directCatalogExportQueueHandler->execute();
    }
}