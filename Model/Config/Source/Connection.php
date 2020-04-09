<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use EffectConnect\Marketplaces\Api\ConnectionRepositoryInterface;
use EffectConnect\Marketplaces\Model\Connection as ConnectionModel;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Connection
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class Connection implements OptionSourceInterface
{
    /**
     * @var ConnectionRepositoryInterface
     */
    protected $_connectionRepository;

    /**
     * Connection constructor.
     *
     * @param ConnectionRepositoryInterface $connectionRepository
     */
    public function __construct(ConnectionRepositoryInterface $connectionRepository)
    {
        $this->_connectionRepository = $connectionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $connectionCollection = $this->_connectionRepository->getList();

        $options = [];

        /** @var ConnectionModel $connection */
        foreach ($connectionCollection->getItems() as $connection) {
            $options[]          = [
                'label'         => $connection->getName() . ' (' . __('ID') . ': ' . $connection->getId() . ')',
                'value'         => strval($connection->getId())
            ];
        }

        return $options;
    }
}
