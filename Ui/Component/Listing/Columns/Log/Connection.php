<?php

namespace EffectConnect\Marketplaces\Ui\Component\Listing\Columns\Log;

use EffectConnect\Marketplaces\Enums\LogSubjectType;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Connection
 * @package EffectConnect\Marketplaces\Ui\Component\Listing\Columns\Log
 */
class Connection extends Column
{
    /**
     * {@inheritdoc}
     */
    const NAME                  = 'connection';

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
                if (!isset($item['connection_id'])) {
                    $item['connection']                 = '-';
                    continue;
                }

                $subjectType                            = LogSubjectType::CONNECTION();

                $item['connection']                     = $subjectType->getLinkHtml(
                    $this->_urlBuilder->getUrl(
                        $subjectType->getUrlPath(),
                        [
                            $subjectType->getIdName()   => $item['connection_id']
                        ]
                    ),
                    $item['connection_id']
                );
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
            $sorting['field'] == static::getName()
        ) {
            $this->getContext()->getDataProvider()->addOrder(
                'connection_id',
                strtoupper($sorting['direction'])
            );
        }
    }
}
