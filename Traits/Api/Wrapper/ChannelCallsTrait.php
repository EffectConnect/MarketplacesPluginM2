<?php

namespace EffectConnect\Marketplaces\Traits\Api\Wrapper;

use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\PHPSdk\Core;
use Exception;

/**
 * Trait ChannelCallsTrait
 * @package EffectConnect\Marketplaces\Traits\Api\Wrapper
 */
trait ChannelCallsTrait
{
    /**
     * @return mixed
     * @throws ApiCallFailedException
     */
    public function getChannels()
    {
        try
        {
            /* @var Core $core */
            $core            = $this->getSdkCore();
            $channelListCall = $core->ChannelListCall();
        }
        catch (Exception $e)
        {
            throw new ApiCallFailedException(__('Fetching channels from EffectConnect failed with message [%1].', $e->getMessage()));
        }

        $apiCall = $channelListCall->read();

        if (!is_null($this->_timeout)) {
            $apiCall->setTimeout($this->_timeout);
        }

        $apiCall->call();

        return $this->getResult($apiCall);
    }
}