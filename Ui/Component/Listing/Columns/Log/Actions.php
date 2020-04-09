<?php

namespace EffectConnect\Marketplaces\Ui\Component\Listing\Columns\Log;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Actions
 * @package EffectConnect\Marketplaces\Ui\Component\Listing\Columns\Log
 */
class Actions extends Column
{
    /**
     * {@inheritdoc}
     */
    const NAME              = 'actions';

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * LogActions Constructor
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
        array $components   = [],
        array $data         = []
    ) {
        $this->_urlBuilder  = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) : array
    {
        $hideCancelButtonJs                     = "
            <script>
                jQuery('.modal-footer .action-secondary.action-dismiss').hide();
                setTimeout(function(){ 
                    jQuery('.modal-popup').scrollTop(0); 
                    jQuery('.modal-popup .modal-inner-wrap').width('90%');
                }, 250);
            </script>
        ";

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as $index => &$item) {
                if (!isset($item['id_field_name'])) {
                    continue;
                }

                $idFieldName                    = $item['id_field_name'];

                if (!isset($item[$idFieldName])) {
                    continue;
                }

                $id                             = $item[$idFieldName];
                $message                        = $item['message'];
                $payload                        = $item['payload'];
                $actionsName                    = $this->getData('name');
                $item[$actionsName]             = [];
                $item[$actionsName]['message']  = [
                    'href' => '#',
                    'label' => __('Show Message'),
                    'confirm' => [
                        'title' => sprintf(__('Log Item %s - Message'), $id),
                        'message' => $hideCancelButtonJs . sprintf('<span>%s</span>', $message)
                    ]
                ];
                $item[$actionsName]['payload']  = [
                    'href' => '#',
                    'label' => __('Show Payload'),
                    'confirm' => [
                        'title' => sprintf(__('Log Item %s - Payload'), $id),
                        'message' => $hideCancelButtonJs . sprintf('
                            <pre style="display: contents; max-width: %s; white-space: normal; overflow: scroll; word-break: break-all;">%s</pre>
                        ', '100%', $payload)
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
