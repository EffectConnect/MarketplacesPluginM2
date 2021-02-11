<?php

namespace EffectConnect\Marketplaces\Objects;

use EffectConnect\Marketplaces\Helper\ApiHelper;
use EffectConnect\Marketplaces\Helper\LogHelper;
use EffectConnect\Marketplaces\Helper\SettingsHelper;
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
     * @var SettingsHelper
     */
    protected $_settingsHelper;

    /**
     * ConnectionApi constructor.
     *
     * @param Connection $connection
     * @param ApiHelper $apiHelper
     * @param LogHelper $logHelper
     * @param SettingsHelper $settingsHelper
     * @throws InvalidKeyException
     */
    public function __construct(
        Connection $connection,
        ApiHelper $apiHelper,
        LogHelper $logHelper,
        SettingsHelper $settingsHelper
    ) {
        $this->_connection      = $connection;
        $this->_apiHelper       = $apiHelper;
        $this->_logHelper       = $logHelper;
        $this->_settingsHelper  = $settingsHelper;

        $this->initializeApiWrapper();
    }

    /**
     * @throws InvalidKeyException
     */
    protected function initializeApiWrapper()
    {
        $publicKey          = $this->_connection->getPublicKey();
        $secretKey          = $this->_connection->getSecretKey();

        $settingsTimeout    = $this->_settingsHelper->getAdvancedApiCallTimeout();
        $timeoutValid       = !is_null($settingsTimeout) && is_numeric($settingsTimeout);
        $timeout            = $timeoutValid ? intval($settingsTimeout) : null;

        $this->_apiWrapper  = new ApiWrapper($publicKey, $secretKey, $timeout);
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
     * Export the offers for these products in the current connection.
     *
     * @param ProductInterface[] $products
     * @return bool
     */
    public function exportSpecificOffers(array $products = null) : bool
    {
        return $this->exportProductsOffersProcedure($this, $products);
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
     * @param TrackingExportDataObject $trackingExportDataObject
     * @return bool
     */
    public function exportShipments(TrackingExportDataObject $trackingExportDataObject)
    {
        return $this->exportShipmentsProcedure($this, $trackingExportDataObject);
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