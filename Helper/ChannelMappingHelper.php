<?php

namespace EffectConnect\Marketplaces\Helper;

use EffectConnect\Marketplaces\Api\ChannelRepositoryInterface;
use EffectConnect\Marketplaces\Api\ConnectionRepositoryInterface;
use EffectConnect\Marketplaces\Enums\LogCode;
use EffectConnect\Marketplaces\Enums\LogSubjectType;
use EffectConnect\Marketplaces\Enums\LogType;
use EffectConnect\Marketplaces\Enums\Process;
use EffectConnect\Marketplaces\Exception\ChannelImportNoConnectionException;
use EffectConnect\Marketplaces\Objects\Loggable;
use EffectConnect\PHPSdk\Core\Model\Response\Channel;
use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class ChannelMappingHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class ChannelMappingHelper extends AbstractHelper
{
    /**
     * @var ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var ConnectionRepositoryInterface
     */
    protected $_connectionRepository;

    /**
     * @var ChannelRepositoryInterface
     */
    protected $_channelRepository;

    /**
     * @var array
     */
    protected $_loggables = [];

    /**
     * ChannelMappingHelper constructor.
     * @param Context $context
     * @param ConnectionRepositoryInterface $connectionRepository
     * @param ChannelRepositoryInterface $channelRepository
     * @param ApiHelper $apiHelper
     */
    public function __construct (
        Context $context,
        ConnectionRepositoryInterface $connectionRepository,
        ChannelRepositoryInterface $channelRepository,
        ApiHelper $apiHelper
    )
    {
        parent::__construct($context);
        $this->_connectionRepository = $connectionRepository;
        $this->_channelRepository    = $channelRepository;
        $this->_apiHelper            = $apiHelper;
    }

    /**
     * @return Loggable[]
     */
    public function getLoggables()
    {
        return $this->_loggables;
    }

    /**
     * @param LogType $logType
     * @param int $connectionId
     * @param string $message
     */
    public function addLoggable(LogType $logType, int $connectionId, string $message)
    {
        $loggable = new Loggable(
            $logType,
            LogCode::CHANNEL_IMPORT_EXECUTED(),
            Process::IMPORT_CHANNELS(),
            $connectionId
        );

        $loggable->setMessage($message);

        $loggable->setSubject(
            LogSubjectType::CONNECTION(),
            $connectionId
        );

        $this->_loggables[] = $loggable;
    }

    /**
     * For all connection fetch channels from EC API and save them in database.
     *
     * @throws ChannelImportNoConnectionException
     */
    public function refreshChannels()
    {
        // Get channels info from each connection.
        $connections = $this->_connectionRepository->getList();
        if ($connections->getTotalCount() == 0)
        {
            throw new ChannelImportNoConnectionException(__('No connections found to fetch channels from.'));
        }

        foreach ($connections->getItems() as $connection)
        {
            $effectConnectChannels = [];

            // Fetch channels for current connection.
            try
            {
                $connectionApi = $this->_apiHelper->getConnectionApi($connection->getEntityId());
                $effectConnectChannelsResponse = $connectionApi->getChannels();
                if ($effectConnectChannelsResponse !== false)
                {
                    $effectConnectChannels = $effectConnectChannelsResponse->getChannels();
                }
            }
            catch (Exception $e)
            {
                $this->addLoggable(LogType::ERROR(), $connection->getEntityId(), __('Channels for connection %1 could not been fetched (error message: %2).', $connection->getName(), $e->getMessage()));
                continue; // Next connection.
            }

            // Sync channel info to local database for current connection.
            if (count($effectConnectChannels) == 0)
            {
                $this->addLoggable(LogType::NOTICE(), $connection->getEntityId(), __('No channels found for connection %1.', $connection->getName()));
            }
            else
            {
                // First fetch all existing channels (to delete channels that are not used anymore).
                $existingChannels = $this->_channelRepository->getListByConnectionId($connection->getEntityId());

                try
                {
                    $addedChannelIds = [];

                    /* @var Channel $effectConnectChannel */
                    foreach ($effectConnectChannels as $effectConnectChannel)
                    {
                        // By default create new channel instance.
                        $channel = $this->_channelRepository->create();

                        // Or does channel already exists in local database? In that case we load it, so we can update it's values.
                        foreach ($existingChannels->getItems() as $existingChannel)
                        {
                            if ($existingChannel->getEcChannelId() == $effectConnectChannel->getId())
                            {
                                $channel = $existingChannel;
                                break;
                            }
                        }

                        // Update channel data.
                        $channel->setConnectionId($connection->getEntityId());
                        $channel->setEcChannelId($effectConnectChannel->getId());
                        $channel->setEcChannelType($effectConnectChannel->getType());
                        $channel->setEcChannelSubtype($effectConnectChannel->getSubtype());
                        $channel->setEcChannelTitle($effectConnectChannel->getTitle());
                        $channel->setEcChannelLanguage($effectConnectChannel->getLanguage());
                        $this->_channelRepository->save($channel);
                        $addedChannelIds[] = $effectConnectChannel->getId();
                    }

                    // Remove channels that are not used anymore.
                    foreach ($existingChannels->getItems() as $existingChannel)
                    {
                        if (!in_array($existingChannel->getEcChannelId(), $addedChannelIds))
                        {
                            $this->_channelRepository->deleteById($existingChannel->getEntityId());
                        }
                    }

                    $this->addLoggable(LogType::SUCCESS(), $connection->getEntityId(), __('Channels for connection %1 successfully refreshed.', $connection->getName()));
                }
                catch(Exception $e)
                {
                    $this->addLoggable(LogType::ERROR(), $connection->getEntityId(), __('Error when saving channels for connection %1 (error message: %2).', $connection->getName(), $e->getMessage()));
                }
            }
        }
    }
}
