<?php

namespace EffectConnect\Marketplaces\Traits\Api\Helper;

use EffectConnect\Marketplaces\Objects\ConnectionApi;
use Exception;

/**
 * Trait LogCallsTrait
 * @package EffectConnect\Marketplaces\Traits\Api\Helper
 */
trait LogCallsTrait
{
    /**
     * @param ConnectionApi $connectionApi
     * @return bool
     */
    protected function isExportLogAllowedProcedure(ConnectionApi $connectionApi)
    {
        $logHelper  = $connectionApi->getLogHelper();
        $apiWrapper = $connectionApi->getApiWrapper();
        $connection = $connectionApi->getConnection();

        try
        {
            // Ask EffectConnect Marketplaces if we are allowed to send a log export.
            return $apiWrapper->readLog()->isPermitted();
        }
        catch (Exception $e)
        {
            $logHelper->logExportAllowedCallError($connection->getEntityId(), $e->getMessage());
        }

        return false;
    }

    /**
     * @param ConnectionApi $connectionApi
     * @return bool
     */
    protected function exportLogProcedure(ConnectionApi $connectionApi)
    {
        $logHelper         = $connectionApi->getLogHelper();
        $apiWrapper        = $connectionApi->getApiWrapper();
        $apiHelper         = $connectionApi->getApiHelper();
        $transformerHelper = $apiHelper->getTransformerHelper();
        $connection        = $connectionApi->getConnection();

        try {
            $xmlFileLocation = $transformerHelper->getSegmentedLogXmlFile($connection);
        } catch (Exception $e) {
            $logHelper->logExportXmlGenerationFailed($connection->getEntityId(), $e->getMessage());
            return false;
        }

        try {
            $apiWrapper->createLog($xmlFileLocation);
        } catch (Exception $e) {
            $logHelper->logExportCreateError($connection->getEntityId(), $e->getMessage());
            return false;
        }

        $logHelper->logLogExportSucceeded(intval($connection->getEntityId()));
        return true;
    }
}