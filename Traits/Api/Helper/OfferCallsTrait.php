<?php

namespace EffectConnect\Marketplaces\Traits\Api\Helper;

use EffectConnect\Marketplaces\Objects\ConnectionApi;
use Exception;
use EffectConnect\Marketplaces\Objects\Api;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Trait OfferCallsTrait
 * @package EffectConnect\Marketplaces\Traits\Api\Helper
 */
trait OfferCallsTrait
{
    /**
     * Upload the whole catalog's offer data (price, cost, stock, delivery-time, etc.) for a specified connection using it's API.
     *
     * @param ConnectionApi $connectionApi
     * @return bool
     */
    protected function exportOffersProcedure(ConnectionApi $connectionApi) : bool
    {
        $logHelper              = $connectionApi->getLogHelper();
        $connection             = $connectionApi->getConnection();

        $logHelper->logOffersExportStarted(intval($connection->getEntityId()));

        $apiHelper              = $connectionApi->getApiHelper();
        $apiWrapper             = $connectionApi->getApiWrapper();
        $transformerHelper      = $apiHelper->getTransformerHelper();

        try {
            $xmlFileLocation    = $transformerHelper->getSegmentedOffersXmlFile($connection);
        } catch (Exception $e) {
            $logHelper->logOffersExportEnded(intval($connection->getEntityId()), false, ['exception' => $e->getMessage()]);
            return false;
        }

        if (!file_exists($xmlFileLocation)) {
            $logHelper->logOffersExportEnded(intval($connection->getEntityId()), false, ['exception' => __('Obtaining the offers XML file (%1) failed.', $xmlFileLocation)]);
            return false;
        }

        try {
            $apiWrapper->updateProducts($xmlFileLocation);
        } catch (Exception $e) {
            $logHelper->logOffersExportEnded(intval($connection->getEntityId()), false, ['exception' => $e->getMessage()]);
            return false;
        }

        $logHelper->logOffersExportEnded(intval($connection->getEntityId()), true);
        return true;
    }

    /**
     * Upload the specific product's offer data (price, cost, stock, delivery-time, etc.) for a specified connection using it's API.
     *
     * @param ConnectionApi $connectionApi
     * @param ProductInterface $product
     * @return bool
     */
    protected function exportProductOffersProcedure(ConnectionApi $connectionApi, ProductInterface $product) : bool
    {
        $logHelper              = $connectionApi->getLogHelper();
        $connection             = $connectionApi->getConnection();
        $apiHelper              = $connectionApi->getApiHelper();
        $apiWrapper             = $connectionApi->getApiWrapper();
        $transformerHelper      = $apiHelper->getTransformerHelper();
        $productId              = $product->getId();

        try {
            $xmlFileLocation    = $transformerHelper->getSegmentedOffersXmlFile($connection, [$product]);
        } catch (Exception $e) {
            $logHelper->logOffersExportProductFailed(intval($connection->getEntityId()), $productId, ['exception' => $e->getMessage()]);
            return false;
        }

        if (!file_exists($xmlFileLocation)) {
            $logHelper->logOffersExportProductFailed(intval($connection->getEntityId()), $productId, ['exception' => __('Obtaining the offers XML file (%1) failed.', $xmlFileLocation)]);
            return false;
        }

        try {
            $apiWrapper->updateProducts($xmlFileLocation);
        } catch (Exception $e) {
            $logHelper->logOffersExportProductFailed(intval($connection->getEntityId()), $productId, ['exception' => $e->getMessage()]);
            return false;
        }

        $logHelper->logOffersExportProductSuccess(intval($connection->getEntityId()), $productId);
        return true;
    }

    /**
     * Upload specific products offer data (price, cost, stock, delivery-time, etc.) for a specified connection using it's API.
     *
     * @param ConnectionApi $connectionApi
     * @param ProductInterface[] $products
     * @return bool
     */
    protected function exportProductsOffersProcedure(ConnectionApi $connectionApi, array $products) : bool
    {
        $logHelper              = $connectionApi->getLogHelper();
        $connection             = $connectionApi->getConnection();
        $apiHelper              = $connectionApi->getApiHelper();
        $apiWrapper             = $connectionApi->getApiWrapper();
        $transformerHelper      = $apiHelper->getTransformerHelper();

        try {
            $xmlFileLocation    = $transformerHelper->getSegmentedOffersXmlFile($connection, $products);
        } catch (Exception $e) {
            $logHelper->logOffersExportConnectionFailed(intval($connection->getEntityId()), ['exception' => $e->getMessage()]);
            return false;
        }

        if (!file_exists($xmlFileLocation)) {
            $logHelper->logOffersExportConnectionFailed(intval($connection->getEntityId()), ['exception' => __('Obtaining the offers XML file (%1) failed.', $xmlFileLocation)]);
            return false;
        }

        try {
            $apiWrapper->updateProducts($xmlFileLocation);
        } catch (Exception $e) {
            $logHelper->logOffersExportConnectionFailed(intval($connection->getEntityId()), ['exception' => $e->getMessage()]);
            return false;
        }

        foreach ($products as $product) {
            $logHelper->logOffersExportProductSuccess(intval($connection->getEntityId()), intval($product->getId()));
        }
        
        return true;
    }
}