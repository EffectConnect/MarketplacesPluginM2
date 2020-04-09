<?php

namespace EffectConnect\Marketplaces\Model\ChannelMapping\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use EffectConnect\Marketplaces\Api\ConnectionRepositoryInterface;

/**
 * Class ConnectionSwitcher
 * @package EffectConnect\Marketplaces\Model\ChannelMapping\Modifier
 */
class ConnectionSwitcher extends AbstractModifier
{
    /**
     * @var ConnectionRepositoryInterface
     */
    protected $_connectionRepository;

    /**
     * ConnectionSwitcher constructor.
     * @param ConnectionRepositoryInterface $connectionRepository
     */
    public function __construct(ConnectionRepositoryInterface $connectionRepository)
    {
        $this->_connectionRepository = $connectionRepository;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta) : array
    {
        $rules = [];

        $connections = $this->_connectionRepository->getList();
        foreach ($connections->getItems() as $connection)
        {
            $rules[] = [
                'value' => intval($connection->getEntityId()),
                'actions' => [
                    [
                        'target'   => 'ec_marketplaces_channelmapping_form.ec_marketplaces_channelmapping_form.channelmapping.channel_id',
                        'callback' => 'filter',
                        'params'   => [
                            strval($connection->getEntityId()),
                            'connection_id'
                        ]
                    ],
                    [
                        'target'   => 'ec_marketplaces_channelmapping_form.ec_marketplaces_channelmapping_form.channelmapping.storeview_id',
                        'callback' => 'filterByConnectionId',
                        'params'   => [
                            strval($connection->getEntityId())
                        ]
                    ]
                ]
            ];
        }

        $meta = array_replace_recursive(
            $meta,
            [
                'channelmapping' => [
                    'children' => [
                        'connection_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'switcherConfig' => [
                                            'enabled' => true,
                                            'rules'   => $rules
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        return $meta;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data) : array
    {
        return $data;
    }
}
