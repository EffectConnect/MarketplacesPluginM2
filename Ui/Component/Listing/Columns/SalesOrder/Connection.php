<?php

namespace EffectConnect\Marketplaces\Ui\Component\Listing\Columns\SalesOrder;

use EffectConnect\Marketplaces\Enums\SubjectType;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Connection
 * @package EffectConnect\Marketplaces\Ui\Component\Listing\Columns\SalesOrder
 */
class Connection extends Column
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Subject Constructor.
     *
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
        array $components       = [],
        array $data             = []
    ) {
        $this->_urlBuilder      = $urlBuilder;
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
                if (!isset($item['ec_marketplaces_connection_id'])) {
                    $item['connection'] = '-';
                    continue;
                }

                $item['connection'] = __('Connection') . ' (' . __('ID') . ': ' . $item['ec_marketplaces_connection_id'] . ')';
            }
        }

        return $dataSource;
    }

    /**
     * {@inheritdoc}
     */
    protected function applySorting()
    {
        $sorting    = $this->getContext()->getRequestParam('sorting');
        $isSortable = $this->getData('config/sortable');

        if (
            $isSortable !== false &&
            !empty($sorting['field']) &&
            !empty($sorting['direction']) &&
            $sorting['field'] == $this->getName()
        ) {
            $this->getContext()->getDataProvider()->addOrder(
                'ec_marketplaces_connection_id',
                strtoupper($sorting['direction'])
            );
        }
    }
}
