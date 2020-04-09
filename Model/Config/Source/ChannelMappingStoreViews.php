<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Api\ConnectionRepositoryInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store;

/**
 * Class ConnectionStoreViews
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class ChannelMappingStoreViews implements OptionSourceInterface
{
    /**
     * @var Store
     */
    protected $_store;

    /**
     * @var ConnectionRepositoryInterface
     */
    protected $_connectionRepository;

    /**
     * ChannelMappingStoreViews constructor.
     * @param Store $store
     * @param ConnectionRepositoryInterface $connectionRepository
     */
    public function __construct(
        Store $store,
        ConnectionRepositoryInterface $connectionRepository
    )
    {
        $this->_store                = $store;
        $this->_connectionRepository = $connectionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];

        // Determine the website that is used in each connection (to be able to filter the storeviews list by chosen connection).
        $connectionIdsByWebsiteId = [];
        $connections = $this->_connectionRepository->getList();
        foreach ($connections->getItems() as $connection)
        {
            $connectionIdsByWebsiteId[$connection->getWebsiteId()][$connection->getEntityId()] = true;
        }

        // List storeviews as single dimension option array (filtering by storeview doesn't work when using optgroups).
        foreach ($this->_store->getWebsiteCollection() as $website) {
            foreach ($this->_store->getGroupCollection() as $group) {
                if ($website->getId() != $group->getWebsiteId()) {
                    continue;
                }
                foreach ($this->_store->getStoreCollection() as $store) {
                    if ($group->getId() != $store->getGroupId()) {
                        continue;
                    }
                    $options[] = [
                        'label'         => $group->getName() . ' > ' . $store->getName(),
                        'value'         => $store->getId(),
                        'connection_id' => isset($connectionIdsByWebsiteId[$website->getId()]) ? array_keys($connectionIdsByWebsiteId[$website->getId()]) : []
                    ];
                }
            }
        }

        return $options;
    }
}
