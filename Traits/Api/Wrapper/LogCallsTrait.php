<?php

namespace EffectConnect\Marketplaces\Traits\Api\Wrapper;

use CURLFile;
use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Objects\ApiWrapper;
use Exception;

/**
 * Trait LogCallsTrait
 * @package EffectConnect\Marketplaces\Traits\Api\Wrapper
 */
trait LogCallsTrait
{
    /**
     * @return mixed
     * @throws ApiCallFailedException
     */
    public function readLog()
    {
        try {
            /** @var ApiWrapper $this */
            $core    = $this->getSdkCore();
            $logCall = $core->LogCall();
        } catch (Exception $e) {
            throw new ApiCallFailedException(__('Log call failed with message [%1].', $e->getMessage()));
        }

        $apiCall = $logCall->read();
        $apiCall->call();

        return $this->getResult($apiCall);
    }

    /**
     * @param $xmlFile
     * @return mixed
     * @throws ApiCallFailedException
     */
    public function createLog($xmlFile)
    {
        if (!file_exists($xmlFile)) {
            throw new ApiCallFailedException(__('Obtaining the log XML file (%1) failed.', $xmlFile));
        }

        try {
            $curlFile = new CURLFile($xmlFile);
        } catch (Exception $e) {
            throw new ApiCallFailedException(__('Obtaining the log XML file (%1) failed.', $xmlFile));
        }

        try {
            /** @var ApiWrapper $this */
            $core    = $this->getSdkCore();
            $logCall = $core->LogCall();
        } catch (Exception $e) {
            throw new ApiCallFailedException(__('Log call failed with message [%1].', $e->getMessage()));
        }

        $apiCall = $logCall->create($curlFile);
        $apiCall->call();

        return $this->getResult($apiCall);
    }
}