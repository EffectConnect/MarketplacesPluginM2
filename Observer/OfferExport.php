<?php

namespace EffectConnect\Marketplaces\Observer;

use EffectConnect\Marketplaces\Objects\QueueHandlers\ProductOfferExportQueueHandler;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class OfferExport
 * @package EffectConnect\Marketplaces\Observer
 */
class OfferExport implements ObserverInterface
{
    /**
     * @var ProductOfferExportQueueHandler
     */
    protected $_productOfferExportQueueHandler;

    /**
     * OfferExport constructor.
     *
     * @param ProductOfferExportQueueHandler $productOfferExportQueueHandler
     */
    public function __construct(ProductOfferExportQueueHandler $productOfferExportQueueHandler) {
        $this->_productOfferExportQueueHandler = $productOfferExportQueueHandler;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->_productOfferExportQueueHandler->schedule(intval($product->getId()));
    }
}
