<?php

namespace EffectConnect\Marketplaces\Helper;

use DOMException;
use EffectConnect\Marketplaces\Exception\CatalogExportGeneratingCatalogXmlFailedException;
use EffectConnect\Marketplaces\Exception\CatalogExportGeneratingCatalogXmlFileFailedException;
use EffectConnect\Marketplaces\Exception\LogExportGeneratingLogXmlFileFailedException;
use EffectConnect\Marketplaces\Exception\OffersExportGeneratingCatalogXmlFileFailedException;
use EffectConnect\Marketplaces\Exception\OrderImportFailedException;
use EffectConnect\Marketplaces\Helper\Transformer\CatalogExportTransformer;
use EffectConnect\Marketplaces\Helper\Transformer\LogExportTransformer;
use EffectConnect\Marketplaces\Helper\Transformer\OffersExportTransformer;
use EffectConnect\Marketplaces\Helper\Transformer\OrderImportTransformer;
use EffectConnect\Marketplaces\Model\Connection;
use EffectConnect\PHPSdk\Core\Model\Response\Order as EffectConnectOrder;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class TransformerHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class TransformerHelper extends AbstractHelper
{
    /**
     * @var CatalogExportTransformer
     */
    protected $_catalogExportTransformer;

    /**
     * @var OffersExportTransformer
     */
    protected $_offersExportTransformer;

    /**
     * @var OrderImportTransformer
     */
    protected $_orderImportTransformer;

    /**
     * @var LogExportTransformer
     */
    protected $_logExportTransformer;

    /**
     * TransformerHelper constructor.
     *
     * @param Context $context
     * @param CatalogExportTransformer $catalogExportTransformer
     * @param OffersExportTransformer $offersExportTransformer
     * @param OrderImportTransformer $orderImportTransformer
     * @param LogExportTransformer $logExportTransformer
     */
    public function __construct(
        Context $context,
        CatalogExportTransformer $catalogExportTransformer,
        OffersExportTransformer $offersExportTransformer,
        OrderImportTransformer $orderImportTransformer,
        LogExportTransformer $logExportTransformer
    ) {
        parent::__construct($context);
        $this->_catalogExportTransformer    = $catalogExportTransformer;
        $this->_offersExportTransformer     = $offersExportTransformer;
        $this->_orderImportTransformer      = $orderImportTransformer;
        $this->_logExportTransformer        = $logExportTransformer;
    }

    /**
     * Get the catalog, transformed to the EffectConnect Marketplaces SDK expected format (as multilevel array).
     *
     * @param Connection $connection
     * @return array
     */
    public function getCatalog(Connection $connection) : array
    {
        return $this->_catalogExportTransformer
            ->get($connection);
    }

    /**
     * Get the catalog, transformed to the EffectConnect Marketplaces SDK expected format (as XML string).
     *
     * @param Connection $connection
     * @return string
     * @throws CatalogExportGeneratingCatalogXmlFailedException
     */
    public function getCatalogXml(Connection $connection) : string
    {
        try {
            return $this->_catalogExportTransformer
                ->getXml($connection);
        } catch (DOMException $e) {
            throw new CatalogExportGeneratingCatalogXmlFailedException(__('Generating the catalog XML for website %1 failed.', intval($connection->getWebsiteId())));
        }
    }

    /**
     * Get the catalog, transformed to the EffectConnect Marketplaces SDK expected format (as XML file location).
     *
     * @param Connection $connection
     * @return string
     * @throws CatalogExportGeneratingCatalogXmlFileFailedException
     */
    public function getCatalogXmlFile(Connection $connection) : string
    {
        $fileLocation = $this->_catalogExportTransformer
            ->saveXml($connection);

        if ($fileLocation === false) {
            throw new CatalogExportGeneratingCatalogXmlFileFailedException(__('Generating the catalog XML file for website %1 failed.', intval($connection->getWebsiteId())));
        }

        return $fileLocation;
    }

    /**
     * Get the catalog, transformed to the EffectConnect Marketplaces SDK expected format (as XML file location).
     * This method builds the XML file in segments per product.
     *
     * @param Connection $connection
     * @return string
     * @throws CatalogExportGeneratingCatalogXmlFileFailedException
     */
    public function getSegmentedCatalogXmlFile(Connection $connection) : string
    {
        $fileLocation = $this->_catalogExportTransformer
            ->saveXmlSegmented($connection);

        if ($fileLocation === false) {
            throw new CatalogExportGeneratingCatalogXmlFileFailedException(__('Generating the catalog XML file for website %1 failed.', intval($connection->getWebsiteId())));
        }

        return $fileLocation;
    }

    /**
     * Get the offers, transformed to the EffectConnect Marketplaces SDK expected format (as multilevel array).
     *
     * @param Connection $connection
     * @return array
     */
    public function getOffers(Connection $connection) : array
    {
        return $this->_offersExportTransformer
            ->get($connection);
    }

    /**
     * Get the offers, transformed to the EffectConnect Marketplaces SDK expected format (as XML string).
     *
     * @param Connection $connection
     * @return string
     * @throws OffersExportGeneratingCatalogXmlFileFailedException
     */
    public function getOffersXml(Connection $connection) : string
    {
        try {
            return $this->_offersExportTransformer
                ->getXml($connection);
        } catch (DOMException $e) {
            throw new OffersExportGeneratingCatalogXmlFileFailedException(__('Generating the offers XML for website %1 failed.', intval($connection->getWebsiteId())));
        }
    }

    /**
     * Get the offers, transformed to the EffectConnect Marketplaces SDK expected format (as XML file location).
     *
     * @param Connection $connection
     * @return string
     * @throws OffersExportGeneratingCatalogXmlFileFailedException
     */
    public function getOffersXmlFile(Connection $connection) : string
    {
        $fileLocation = $this->_offersExportTransformer
            ->saveXml($connection);

        if ($fileLocation === false) {
            throw new OffersExportGeneratingCatalogXmlFileFailedException(__('Generating the offers XML file for website %1 failed.', intval($connection->getWebsiteId())));
        }

        return $fileLocation;
    }

    /**
     * Get the offers, transformed to the EffectConnect Marketplaces SDK expected format (as XML file location).
     * This method builds the XML file in segments per product.
     *
     * @param Connection $connection
     * @param ProductInterface[] $catalog
     * @return string
     * @throws OffersExportGeneratingCatalogXmlFileFailedException
     */
    public function getSegmentedOffersXmlFile(Connection $connection, array $catalog = null) : string
    {
        $fileLocation = $this->_offersExportTransformer
            ->saveXmlSegmented($connection, 'offers', $catalog);

        if ($fileLocation === false) {
            throw new OffersExportGeneratingCatalogXmlFileFailedException(__('Generating the offers XML file for website %1 failed.', intval($connection->getWebsiteId())));
        }

        return $fileLocation;
    }

    /**
     * @param Connection $connection
     * @param EffectConnectOrder $effectConnectOrder
     * @return bool|OrderInterface
     * @throws OrderImportFailedException
     */
    public function importOrder(Connection $connection, EffectConnectOrder $effectConnectOrder)
    {
        return $this->_orderImportTransformer->importOrder($connection, $effectConnectOrder);
    }

    /**
     * Get the log, transformed to the EffectConnect Marketplaces SDK expected format (as XML file location).
     * This method builds the XML file in segments per log item.
     *
     * @param Connection $connection
     * @return string
     * @throws LogExportGeneratingLogXmlFileFailedException
     */
    public function getSegmentedLogXmlFile(Connection $connection) : string
    {
        $fileLocation = $this->_logExportTransformer
            ->saveXmlSegmented($connection);

        if ($fileLocation === false) {
            throw new LogExportGeneratingLogXmlFileFailedException(__('Generating the log XML file for connection failed.'));
        }

        return $fileLocation;
    }
}
