<?php

namespace EffectConnect\Marketplaces\Objects;

use EffectConnect\Marketplaces\Helper\ApiHelper;
use EffectConnect\Marketplaces\Helper\LogHelper;
use EffectConnect\Marketplaces\Model\Connection;
use EffectConnect\Marketplaces\Model\Log;
use EffectConnect\Marketplaces\Model\OrderLine;
use EffectConnect\Marketplaces\Traits\Api\Helper\CatalogCallsTrait;
use EffectConnect\Marketplaces\Traits\Api\Helper\ChannelCallsTrait;
use EffectConnect\Marketplaces\Traits\Api\Helper\LogCallsTrait;
use EffectConnect\Marketplaces\Traits\Api\Helper\OfferCallsTrait;
use EffectConnect\Marketplaces\Traits\Api\Helper\OrderCallsTrait;
use EffectConnect\Marketplaces\Traits\Api\Helper\ShipmentExportCallsTrait;
use EffectConnect\PHPSdk\Core\Interfaces\ResponseContainerInterface;
use EffectConnect\PHPSdk\Core\Exception\InvalidKeyException;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;

/**
 * Class ConnectionApi
 * @package EffectConnect\Marketplaces\Objects
 */
class ConnectionApi
{
    use CatalogCallsTrait,
        OfferCallsTrait,
        OrderCallsTrait,
        ShipmentExportCallsTrait,
        ChannelCallsTrait,
        LogCallsTrait;

    /**
     * @var Connection
     */
    protected $_connection;

    /**
     * @var ApiWrapper
     */
    protected $_apiHelper;

    /**
     * @var ApiWrapper
     */
    protected $_apiWrapper;

    /**
     * @var LogHelper
     */
    protected $_logHelper;

    /**
     * ConnectionApi constructor.
     *
     * @param Connection $connection
     * @param ApiHelper $apiHelper
     * @param LogHelper $logHelper
     * @throws InvalidKeyException
     */
    public function __construct(
        Connection $connection,
        ApiHelper $apiHelper,
        LogHelper $logHelper
    ) {
        $this->_connection  = $connection;
        $this->_apiHelper   = $apiHelper;
        $this->_logHelper   = $logHelper;

        $this->initializeApiWrapper();
    }

    /**
     * @throws InvalidKeyException
     */
    protected function initializeApiWrapper()
    {
        $publicKey          = $this->_connection->getPublicKey();
        $secretKey          = $this->_connection->getSecretKey();

        $this->_apiWrapper = new ApiWrapper($publicKey, $secretKey);
    }

    /**
     * @return Connection
     */
    public function getConnection() : Connection
    {
        return $this->_connection;
    }

    /**
     * @return ApiHelper
     */
    public function getApiHelper() : ApiHelper
    {
        return $this->_apiHelper;
    }

    /**
     * @return ApiWrapper
     */
    public function getApiWrapper() : ApiWrapper
    {
        return $this->_apiWrapper;
    }

    /**
     * @return LogHelper
     */
    public function getLogHelper() : LogHelper
    {
        return $this->_logHelper;
    }

    /**
     * Export the catalog for the current connection.
     *
     * @return bool
     */
    public function exportCatalog() : bool
    {
        return $this->exportCatalogProcedure($this);
    }

    /**
     * Export the offers for the current connection.
     *
     * @return bool
     */
    public function exportOffers() : bool
    {
        return $this->exportOffersProcedure($this);
    }

    /**
     * Export the offer for this product in the current connection.
     *
     * @param ProductInterface $product
     * @return bool
     */
    public function exportOffer(ProductInterface $product = null) : bool
    {
        return $this->exportProductOffersProcedure($this, $product);
    }

    /**
     * @return bool|ResponseContainerInterface
     */
    public function getChannels()
    {
        return $this->getChannelsProcedure($this);
    }

    /**
     * @return bool
     */
    public function importOrders()
    {
        return $this->importOrdersProcedure($this);
    }

    /**
     * @param OrderInterface $order
     * @param ShipmentTrackInterface $shipmentTrack
     * @param OrderLine $ecOrderLine
     * @return bool
     */
    public function exportShipment(OrderInterface $order, ShipmentTrackInterface $shipmentTrack, OrderLine $ecOrderLine)
    {
        return $this->exportShipmentProcedure($this, $order, $shipmentTrack, $ecOrderLine);
    }

    /**
     * @return bool
     */
    public function isExportLogAllowed()
    {
        return $this->isExportLogAllowedProcedure($this);
    }

    /**
     * @return bool
     */
    public function exportLog()
    {
        return $this->exportLogProcedure($this);
    }
}