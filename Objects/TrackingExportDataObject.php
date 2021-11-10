<?php

namespace EffectConnect\Marketplaces\Objects;

use Countable;
use EffectConnect\Marketplaces\Model\OrderLine;
use Iterator;
use Magento\Sales\Api\Data\ShipmentTrackInterface;

/**
 * Class TrackingExportDataObject
 * @package EffectConnect\Marketplaces\Objects
 */
class TrackingExportDataObject implements Iterator, Countable
{
    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param OrderLine $orderLine
     * @param ShipmentTrackInterface|null $shipmentTrack
     */
    public function addTrackingExportData(OrderLine $orderLine, ShipmentTrackInterface $shipmentTrack = null)
    {
        $this->data[] = [$shipmentTrack, $orderLine];
    }

    /**
     * @return ShipmentTrackInterface|null
     */
    public function getCurrentShipmentTrack()
    {
        $shipmentTrack = null;
        if ($this->valid()) {
            list($shipmentTrack, $orderLine) = $this->current();
        }
        return $shipmentTrack;
    }

    /**
     * @return OrderLine|null
     */
    public function getCurrentOrderLine()
    {
        $orderLine = null;
        if ($this->valid()) {
            list($shipmentTrack, $orderLine) = $this->current();
        }
        return $orderLine;
    }

    public function current()
    {
        if ($this->valid()) {
            return $this->data[$this->position];
        }
        return [];
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return isset($this->data[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function next()
    {
        $this->position++;
    }

    public function count()
    {
        return count($this->data);
    }
}