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

    public function current(): array
    {
        if ($this->valid()) {
            return $this->data[$this->position];
        }
        return [];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function next(): void
    {
        $this->position++;
    }

    public function count(): int
    {
        return count($this->data);
    }
}