<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Api\ConnectionRepositoryInterface;
use EffectConnect\Marketplaces\Model\Connection as ConnectionModel;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ConnectionsThatHaveChannels
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class ConnectionsThatHaveChannels implements OptionSourceInterface
{
    /**
     * @var ConnectionRepositoryInterface
     */
    protected $_connectionRepository;

    /**
     * ConnectionsThatHaveChannels constructor.
     *
     * @param ConnectionRepositoryInterface $connectionRepository
     */
    public function __construct(ConnectionRepositoryInterface $connectionRepository) {
        $this->_connectionRepository = $connectionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options              = [];
        $connectionCollection = $this->_connectionRepository->getList();

        /** @var ConnectionModel $connection */
        foreach ($connectionCollection->getItems() as $connection)
        {
            // Only get connections that have channels.
            if (count($connection->getChannels()) > 0)
            {
                $options[] = [
                    'label' => $connection->getName() . ' (' . __('ID') . ': ' . $connection->getId() . ')',
                    'value' => strval($connection->getId())
                ];
            }
        }

        return $options;
    }
}
