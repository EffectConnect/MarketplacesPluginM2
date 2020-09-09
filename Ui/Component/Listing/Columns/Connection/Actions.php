<?php

namespace EffectConnect\Marketplaces\Ui\Component\Listing\Columns\Connection;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Actions
 * @package EffectConnect\Marketplaces\Ui\Component\Listing\Columns\Connection
 */
class Actions extends Column
{
    const URL_PATH_EDIT             = 'ec_marketplaces/connection/edit';
    const URL_PATH_DELETE           = 'ec_marketplaces/connection/delete';
    const URL_PATH_EXPORT_CATALOG   = 'ec_marketplaces/connection/exportcatalog';
    const URL_PATH_EXPORT_LOG       = 'ec_marketplaces/connection/exportlog';

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->_urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'entity_id' => $item['entity_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->_urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'entity_id' => $item['entity_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => sprintf(__('Delete') . ' %s', $item['name']),
                                'message' => sprintf(__('Are you sure you want to delete connection') . ' %s?', $item['name'])
                            ]
                        ],
                        'export_catalog' => [
                            'href' => $this->_urlBuilder->getUrl(
                                static::URL_PATH_EXPORT_CATALOG,
                                [
                                    'entity_id' => $item['entity_id']
                                ]
                            ),
                            'label' => __('Export Catalog'),
                            'confirm' => [
                                'title' => sprintf(__('Export catalog for') . ' %s', $item['name']),
                                'message' => sprintf(__('Are you sure you want to export the catalog for connection') . ' %s?', $item['name'])
                            ]
                        ],
                        'export_log' => [
                            'href' => $this->_urlBuilder->getUrl(
                                static::URL_PATH_EXPORT_LOG,
                                [
                                    'entity_id' => $item['entity_id']
                                ]
                            ),
                            'label' => __('Export Log'),
                            'confirm' => [
                                'title' => sprintf(__('Export log for') . ' %s', $item['name']),
                                'message' => sprintf(__('Are you sure you want to export the log for connection') . ' %s?', $item['name'])
                            ]
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
