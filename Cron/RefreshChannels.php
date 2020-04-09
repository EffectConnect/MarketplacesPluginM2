<?php

namespace EffectConnect\Marketplaces\Cron;

use EffectConnect\Marketplaces\Exception\ChannelImportNoConnectionException;
use EffectConnect\Marketplaces\Helper\ChannelMappingHelper;
use EffectConnect\Marketplaces\Helper\LogHelper;

/**
 * Class RefreshChannels
 * @package EffectConnect\Marketplaces\Cron
 */
class RefreshChannels
{
    /**
     * @var ChannelMappingHelper
     */
    protected $_channelMappingHelper;

    /**
     * @var LogHelper
     */
    protected $_logHelper;

    /**
     * RefreshChannels constructor.
     * @param ChannelMappingHelper $channelMappingHelper
     * @param LogHelper $logHelper
     */
    public function __construct(
        ChannelMappingHelper $channelMappingHelper,
        LogHelper $logHelper
    )
    {
        $this->_channelMappingHelper = $channelMappingHelper;
        $this->_logHelper            = $logHelper;
    }

    /**
     * For all connection fetch channels from EC API and save them in database.
     */
    public function execute()
    {
        // For all connection fetch channels from EC API and save them in database.
        try
        {
            $this->_channelMappingHelper->refreshChannels();
        }
        catch (ChannelImportNoConnectionException $e)
        {
            // Do nothing, it's no problem that we can't fetch channels if there are no connections.
        }

        // Log feedback messages.
        foreach ($this->_channelMappingHelper->getLoggables() as $loggable)
        {
            $this->_logHelper->insertLogItem($loggable);
        }
    }
}
