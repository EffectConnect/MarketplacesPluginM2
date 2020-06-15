<?php

namespace EffectConnect\Marketplaces\Ui\Component\Listing\Columns\ChannelMapping;

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Customer
 * @package EffectConnect\Marketplaces\Ui\Component\Listing\Columns\ChannelMapping
 */
class Customer extends Column
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'customer';

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var CustomerRepository
     */
    protected $_customerRepository;

    /**
     * Subject Constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param CustomerRepository $customerRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        CustomerRepository $customerRepository,
        array $components       = [],
        array $data             = []
    ) {
        $this->_urlBuilder          = $urlBuilder;
        $this->_customerRepository  = $customerRepository;
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
                if (!isset($item['customer_id'])) {
                    $item['customer'] = '';
                    continue;
                }

                $customerId = intval($item['customer_id']);

                try {
                    $customer = $this->_customerRepository->getById($customerId);
                } catch (NoSuchEntityException $e) {
                    $item['customer'] = __('Customer does not exist') . ' (' . __('ID') . ': ' . $item['customer_id'] . ')';
                    continue;
                } catch (LocalizedException $e) {
                    $item['customer'] = '- (' . __('ID') . ': ' . $item['customer_id'] . ')';
                    continue;
                }

                $label = $customer->getFirstname() . ' ' . $customer->getLastname() . ' (' . __('ID') . ': ' . $item['customer_id'] . ')';
                $url   = $this->_urlBuilder->getUrl('customer/index/edit', [
                    'id' => $item['customer_id']
                ]);

                $html             = html_entity_decode('<a href="' . $url . '">' . $label . '</a>');
                $item['customer'] = $html;
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
                'customer_id',
                strtoupper($sorting['direction'])
            );
        }
    }
}
