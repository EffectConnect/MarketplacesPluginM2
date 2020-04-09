<?php

namespace EffectConnect\Marketplaces\Traits\Api\Helper;

use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Objects\ConnectionApi;

/**
 * Trait ChannelCallsTrait
 * @package EffectConnect\Marketplaces\Traits\Api\Helper
 */
trait ChannelCallsTrait
{
    /**
     * @param ConnectionApi $connectionApi
     * @return bool|mixed
     */
    protected function getChannelsProcedure(ConnectionApi $connectionApi)
    {
        $apiWrapper = $connectionApi->getApiWrapper();

        try
        {
            $channels = $apiWrapper->getChannels();
        }
        catch (ApiCallFailedException $e)
        {
            return false;
        }

        return $channels;
    }
}