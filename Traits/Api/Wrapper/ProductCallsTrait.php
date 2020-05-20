<?php

namespace EffectConnect\Marketplaces\Traits\Api\Wrapper;

use CURLFile;
use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Exception\CatalogExportObtainingXmlFileFailedException;
use EffectConnect\Marketplaces\Exception\OffersExportGeneratingCatalogXmlFileFailedException;
use EffectConnect\Marketplaces\Objects\ApiWrapper;
use Exception;
use EffectConnect\PHPSdk\Core\Model\Response\Response;

/**
 * Trait ProductCallsTrait
 * @package EffectConnect\Marketplaces\Traits\Api\Wrapper
 */
trait ProductCallsTrait
{
    /**
     * Perform the create products call using the generated XML file.
     *
     * @param string $xmlFile
     * @return void
     * @throws ApiCallFailedException
     * @throws CatalogExportObtainingXmlFileFailedException
     */
    public function createProducts(string $xmlFile)
    {
        /** @var ApiWrapper $this */
        $core           = $this->getSdkCore();
        $productsCall   = $core->ProductsCall();

        if (!file_exists($xmlFile)) {
            throw new CatalogExportObtainingXmlFileFailedException(__('Obtaining the offers XML file (%1) failed.', $xmlFile));
        }

        try {
            $curlFile   = new CURLFile($xmlFile);
        } catch (Exception $e) {
            throw new CatalogExportObtainingXmlFileFailedException(__('Obtaining the offers XML file (%1) failed.', $xmlFile));
        }

        $apiCall        = $productsCall->create($curlFile);

        if (!is_null($this->_timeout)) {
            $apiCall->setTimeout($this->_timeout);
        }

        $apiCall->call();

        if (!$apiCall->isSuccess()) {
            $errorsString       = '';

            foreach ($apiCall->getErrors() as $error) {
                $errorsString  .= (empty($errorsString) ? '' : PHP_EOL) . $error;
            }

            throw new ApiCallFailedException(__('The create product api call failed for the following reasons: %1', $errorsString));
        }

        $response       = $apiCall->getResponseContainer();
        $result         = $response->getResponse()->getResult();

        switch ($result) {
            case Response::STATUS_FAILURE:
                throw new ApiCallFailedException(__('The create product api call failed for an unknown reason.'));
            case Response::STATUS_WARNING:
            case Response::STATUS_SUCCESS:
            default:
                return;
        }
    }

    /**
     * Perform the update products call using the generated XML file.
     *
     * @param string $xmlFile
     * @return void
     * @throws ApiCallFailedException
     * @throws OffersExportGeneratingCatalogXmlFileFailedException
     */
    public function updateProducts(string $xmlFile)
    {
        /** @var ApiWrapper $this */
        $core           = $this->getSdkCore();
        $productsCall   = $core->ProductsCall();

        if (!file_exists($xmlFile)) {
            throw new OffersExportGeneratingCatalogXmlFileFailedException(__('Obtaining the offers XML file (%1) failed.', $xmlFile));
        }

        try {
            $curlFile   = new CURLFile($xmlFile);
        } catch (Exception $e) {
            throw new OffersExportGeneratingCatalogXmlFileFailedException(__('Obtaining the offers XML file (%1) failed.', $xmlFile));
        }

        $apiCall        = $productsCall->update($curlFile);

        if (!is_null($this->_timeout)) {
            $apiCall->setTimeout($this->_timeout);
        }

        $apiCall->call();

        if (!$apiCall->isSuccess()) {
            $errorsString       = '';

            foreach ($apiCall->getErrors() as $error) {
                $errorsString  .= (empty($errorsString) ? '' : PHP_EOL) . $error;
            }

            throw new ApiCallFailedException(__('The update product api call failed for the following reasons: %1', $errorsString));
        }

        $response       = $apiCall->getResponseContainer();
        $result         = $response->getResponse()->getResult();

        switch ($result) {
            case Response::STATUS_FAILURE:
                throw new ApiCallFailedException(__('The update product api call failed for an unknown reason.'));
            case Response::STATUS_WARNING:
            case Response::STATUS_SUCCESS:
            default:
                return;
        }
    }
}