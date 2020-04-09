<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Api\ChannelRepositoryInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Channels
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class Channels implements OptionSourceInterface
{
    /**
     * @var ChannelRepositoryInterface
     */
    protected $_channelRepository;

    /**
     * Channels constructor.
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->_channelRepository = $channelRepository;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $channelArray = [];

        $channels = $this->_channelRepository->getList();
        foreach ($channels->getItems() as $channel)
        {
            $channelArray[] = [
                'label'         => $channel->getEcChannelTitle() . ' (' . __('ID') . ': ' . $channel->getEcChannelId() . ', ' . __('Type') . ': ' . $channel->getEcChannelType() . ')',
                'value'         => $channel->getEntityId(),
                'connection_id' => $channel->getConnectionId(),
            ];
        }

        return $channelArray;
    }
}

