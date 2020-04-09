<?php

namespace EffectConnect\Marketplaces\Cron;

use EffectConnect\Marketplaces\Objects\QueueHandlers\ProductOfferExportQueueHandler;

/**
 * Class HandleProductOfferExportQueue
 * @package EffectConnect\Marketplaces\Cron
 */
class HandleProductOfferExportQueue {
    /**
     * @var ProductOfferExportQueueHandler
     */
    protected $_productOfferExportQueueHandler;

    /**
     * HandleProductOfferExportQueue constructor.
     *
     * @param ProductOfferExportQueueHandler $productOfferExportQueueHandler
     */
    public function __construct(ProductOfferExportQueueHandler $productOfferExportQueueHandler) {
        $this->_productOfferExportQueueHandler = $productOfferExportQueueHandler;
    }

    /**
     * Executes when called by the cronjob.
     */
    public function execute()
    {
        $this->_productOfferExportQueueHandler->execute();
    }
}