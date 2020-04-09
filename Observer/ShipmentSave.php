<?php

namespace EffectConnect\Marketplaces\Observer;

use EffectConnect\Marketplaces\Api\OrderLineRepositoryInterface;
use EffectConnect\Marketplaces\Helper\ApiHelper;
use EffectConnect\Marketplaces\Helper\LogHelper;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ShipmentSave
 * @package EffectConnect\Marketplaces\Observer
 */
class ShipmentSave implements ObserverInterface
{
    /**
     * @var ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var OrderLineRepositoryInterface
     */
    protected $_orderLineRepository;

    /**
     * @var LogHelper
     */
    protected $_logHelper;

    /**
     * ShipmentExport constructor.
     * @param ApiHelper $apiHelper
     * @param LogHelper $logHelper
     * @param OrderLineRepositoryInterface $orderLineRepositoryInterface
     */
    public function __construct(
        ApiHelper $apiHelper,
        LogHelper $logHelper,
        OrderLineRepositoryInterface $orderLineRepositoryInterface
    ) {
        $this->_apiHelper           = $apiHelper;
        $this->_logHelper           = $logHelper;
        $this->_orderLineRepository = $orderLineRepositoryInterface;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $shipment   = $observer->getEvent()->getShipment();
        $shipmentId = intval($shipment->getId());

        try
        {
            // Save current shipment id to the ec_marketplaces_order_lines database if not already present (otherwise we might assign a Magento order line to a EC order line multiple times).
            if ($this->_orderLineRepository->getListByShipmentId($shipmentId)->getTotalCount() == 0)
            {
                // For each shipment item, find the corresponding EffectConnect order line.
                foreach ($shipment->getAllItems() as $shipmentItem)
                {
                    $quoteItemId  = $shipmentItem->getOrderItem()->getQuoteItemId();
                    $ecOrderLines = $this->_orderLineRepository->getListByQuoteItemId($quoteItemId)->getItems();

                    // Assign as many EC order lines as there are items in the shipment.
                    $qtyShipped   = intval($shipmentItem->getQty());
                    $qtyAssigned  = 0;
                    foreach ($ecOrderLines as $ecOrderLine)
                    {
                        // Only assign order lines that haven't been assigned to a shipment yet.
                        if (intval($ecOrderLine->getShipmentId()) == 0 && $qtyAssigned < $qtyShipped) {
                            $ecOrderLine->setShipmentId($shipmentId);
                            $this->_orderLineRepository->save($ecOrderLine);
                            $qtyAssigned++;
                        }
                    }
                }
            }
        }
        catch (Exception $e)
        {
            $this->_logHelper->logSaveShipmentFailed($shipment, $e->getMessage());
            return;
        }
    }
}
