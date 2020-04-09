<?php

namespace EffectConnect\Marketplaces\Model\Connection\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManager;

/**
 * Class WebsiteSwitcher
 * @package EffectConnect\Marketplaces\Model\Connection\Modifier
 */
class WebsiteSwitcher extends AbstractModifier
{
    /**
     * @var StoreManager
     */
    protected $_storeManager;

    /**
     * WebsiteSwitcher constructor.
     * @param StoreManager $storeManager
     */
    public function __construct(StoreManager $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta) : array
    {
        $rules = [];

        /** @var WebsiteInterface $website */
        foreach ($this->_storeManager->getWebsites() as $website)
        {
            $rules[] = [
                'value' => intval($website->getId()),
                'actions' => [
                    [
                        'target'   => 'ec_marketplaces_connection_form.ec_marketplaces_connection_form.connection.catalog_export.image_url_storeview_id',
                        'callback' => 'filter',
                        'params'   => [
                            strval($website->getId()),
                            'website_id'
                        ]
                    ],
                    [
                        'target'   => 'ec_marketplaces_connection_form.ec_marketplaces_connection_form.connection.catalog_export.storeview_mapping',
                        'callback' => 'filterByWebsiteId',
                        'params'   => [
                            strval($website->getId()),
                        ]
                    ]
                ]
            ];
        }

        $meta = array_replace_recursive(
            $meta,
            [
                'connection' => [
                    'children' => [
                        'catalog_export' => [
                            'children' => [
                                'website_id' => [
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
