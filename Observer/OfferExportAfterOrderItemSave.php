<?php

namespace EffectConnect\Marketplaces\Observer;

use EffectConnect\Marketplaces\Objects\QueueHandlers\ProductOfferExportQueueHandler;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Item;

/**
 * Class OfferExportAfterOrderItemCancel
 * @package EffectConnect\Marketplaces\Observer
 */
class OfferExportAfterOrderItemSave implements ObserverInterface
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
        /** @var Item $item */
        $item = $observer->getEvent()->getItem();
        if ($item instanceof Item) {
            $this->_productOfferExportQueueHandler->schedule(intval($item->getProductId()));
        }
    }
}
