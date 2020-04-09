<?php

namespace EffectConnect\Marketplaces\Traits\Api\Helper;

use EffectConnect\Marketplaces\Objects\ConnectionApi;
use Exception;
use EffectConnect\Marketplaces\Objects\Api;

/**
 * Trait CatalogCallsTrait
 * @package EffectConnect\Marketplaces\Traits\Api\Helper
 */
trait CatalogCallsTrait
{
    /**
     * Upload the catalog for a specified connection using it's API.
     *
     * @param ConnectionApi $connectionApi
     * @return bool
     */
    protected function exportCatalogProcedure(ConnectionApi $connectionApi) : bool
    {
        $logHelper              = $connectionApi->getLogHelper();
        $connection             = $connectionApi->getConnection();

        $logHelper->logCatalogExportStarted(intval($connection->getEntityId()));

        $apiHelper              = $connectionApi->getApiHelper();
        $apiWrapper             = $connectionApi->getApiWrapper();
        $transformerHelper      = $apiHelper->getTransformerHelper();

        try {
            $xmlFileLocation    = $transformerHelper->getSegmentedCatalogXmlFile($connection);
        } catch (Exception $e) {
            $logHelper->logCatalogExportEnded(intval($connection->getEntityId()), false, ['exception' => $e->getMessage()]);
            return false;
        }

        if (!file_exists($xmlFileLocation)) {
            $logHelper->logCatalogExportEnded(intval($connection->getEntityId()), false, ['exception' => __('Obtaining the catalog XML file (%1) failed.', $xmlFileLocation)]);
            return false;
        }

        try {
            $apiWrapper->createProducts($xmlFileLocation);
        } catch (Exception $e) {
            $logHelper->logCatalogExportEnded(intval($connection->getEntityId()), false, ['exception' => $e->getMessage()]);
            return false;
        }

        $logHelper->logCatalogExportEnded(intval($connection->getEntityId()), true);
        return true;
    }
}