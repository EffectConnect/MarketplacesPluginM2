<?php

namespace EffectConnect\Marketplaces\Observer;

use DateTime;
use EffectConnect\Marketplaces\Api\OrderLineRepositoryInterface;
use EffectConnect\Marketplaces\Enums\ShipmentEvent as ShipmentEventEnum;
use EffectConnect\Marketplaces\Helper\ApiHelper;
use EffectConnect\Marketplaces\Helper\LogHelper;
use EffectConnect\Marketplaces\Helper\SettingsHelper;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

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
     * @var SettingsHelper
     */
    protected $_settingsHelper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * ShipmentExport constructor.
     * @param ApiHelper $apiHelper
     * @param LogHelper $logHelper
     * @param OrderLineRepositoryInterface $orderLineRepositoryInterface
     * @param SettingsHelper $settingsHelper
     * @param OrderRepositoryInterface $orderRepositoryInterface
     */
    public function __construct(
        ApiHelper $apiHelper,
        LogHelper $logHelper,
        OrderLineRepositoryInterface $orderLineRepositoryInterface,
        SettingsHelper $settingsHelper,
        OrderRepositoryInterface $orderRepositoryInterface,
    ) {
        $this->_apiHelper           = $apiHelper;
        $this->_logHelper           = $logHelper;
        $this->_orderLineRepository = $orderLineRepositoryInterface;
        $this->_settingsHelper      = $settingsHelper;
        $this->_orderRepository     = $orderRepositoryInterface;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $shipment   = $observer->getEvent()->getShipment();
        $shipmentId = intval($shipment->getId());
        $orderId    = intval($shipment->getOrderId());

        try
        {
            // Save current shipment id to the ec_marketplaces_order_lines database if not already present (otherwise we might assign a Magento order line to a EC order line multiple times).
            if ($this->_orderLineRepository->getListByShipmentId($shipmentId)->getTotalCount() == 0)
            {
                // Orders older than 28 days can not be processed anymore by EC
                $order = $this->_orderRepository->get($orderId);
                $orderDate = new DateTime($order->getCreatedAt());
                if ($orderDate->diff(new DateTime())->days <= 28) {
                    // For each shipment item, find the corresponding EffectConnect order line.
                    foreach ($shipment->getAllItems() as $shipmentItem)
                    {
                        $quoteItemId = intval($shipmentItem->getOrderItem()->getQuoteItemId());
                        if ($quoteItemId > 0) {
                            $ecOrderLines = $this->_orderLineRepository->getListByQuoteItemId($quoteItemId)->getItems();

                            // Assign as many EC order lines as there are items in the shipment.
                            $qtyShipped   = intval($shipmentItem->getQty());
                            $qtyAssigned  = 0;
                            foreach ($ecOrderLines as $ecOrderLine)
                            {
                                // Only assign order lines that haven't been assigned to a shipment yet.
                                if (intval($ecOrderLine->getShipmentId()) === 0 && $qtyAssigned < $qtyShipped) {
                                    $ecOrderLine->setShipmentId($shipmentId);

                                    // In case shipment_export_settings/event is set to 'shipment, then we this order line may be exported (track_id is not leading anymore)
                                    if (strval($this->_settingsHelper->getShipmentExportEvent()) === strval(ShipmentEventEnum::SHIPMENT())) {
                                        $ecOrderLine->setExport(1);
                                    }

                                    $this->_orderLineRepository->save($ecOrderLine);
                                    $qtyAssigned++;
                                }
                            }
                        }
                    }
                } else {
                    $this->_logHelper->logSaveShipmentSkipped($shipment, __('EffectConnect does not accept shipments for orders older than 30 days'));
                    return;
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
