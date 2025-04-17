<?php

namespace EffectConnect\Marketplaces\Objects\QueueHandlers;

use EffectConnect\Marketplaces\Api\ConnectionRepositoryInterface;
use EffectConnect\Marketplaces\Api\ProductOfferExportQueueItemRepositoryInterface;
use EffectConnect\Marketplaces\Helper\ApiHelper;
use EffectConnect\Marketplaces\Helper\LogHelper;
use EffectConnect\Marketplaces\Helper\SettingsHelper;
use EffectConnect\Marketplaces\Interfaces\QueueHandlerInterface;
use EffectConnect\Marketplaces\Model\Connection;
use EffectConnect\Marketplaces\Model\ProductOfferExportQueueItem;
use EffectConnect\Marketplaces\Objects\ConnectionApi;
use EffectConnect\PHPSdk\Core\Exception\InvalidKeyException;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class ProductOfferExportQueueHandler
 * @package EffectConnect\Marketplaces\Objects\QueueHandlers
 */
class ProductOfferExportQueueHandler implements QueueHandlerInterface
{
    /**
     * @var ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var ConnectionRepositoryInterface
     */
    protected $_connectionRepository;

    /**
     * @var ProductOfferExportQueueItemRepositoryInterface
     */
    protected $_productOfferExportQueueItemRepository;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    protected $_searchCriteriaBuilderFactory;

    /**
     * @var SettingsHelper
     */
    protected $_settingsHelper;

    /**
     * @var LogHelper
     */
    protected $_logHelper;

    /**
     * @var Configurable
     */
    protected $_configurableType;

    /**
     * ShipmentExport constructor.
     *
     * @param ApiHelper $apiHelper
     * @param ConnectionRepositoryInterface $connectionRepository
     * @param ProductOfferExportQueueItemRepositoryInterface $productOfferExportQueueItemRepository
     * @param ProductRepository $productRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param SettingsHelper $settingsHelper
     * @param LogHelper $logHelper
     * @param Configurable $configurableType

     */
    public function __construct(
        ApiHelper $apiHelper,
        ConnectionRepositoryInterface $connectionRepository,
        ProductOfferExportQueueItemRepositoryInterface $productOfferExportQueueItemRepository,
        ProductRepository $productRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        SettingsHelper $settingsHelper,
        LogHelper $logHelper,
        Configurable $configurableType
    ) {
        $this->_apiHelper                               = $apiHelper;
        $this->_connectionRepository                    = $connectionRepository;
        $this->_productOfferExportQueueItemRepository   = $productOfferExportQueueItemRepository;
        $this->_productRepository                       = $productRepository;
        $this->_searchCriteriaBuilderFactory            = $searchCriteriaBuilderFactory;
        $this->_settingsHelper                          = $settingsHelper;
        $this->_logHelper                               = $logHelper;
        $this->_configurableType                        = $configurableType;
    }

    /**
     * Schedule an item to be picked up by the queue handler.
     *
     * @param int $productId
     * @return void
     */
    public function schedule(int $productId)
    {
        if (!boolval($this->_settingsHelper->getOfferExportExportOnChange() ?? false)) {
            return;
        }

        // In case of a configurable product, the parent product ID is queued instead of the given product ID,
        // because the exporter does not allow to export children as standalone product.
        $parentIds         = $this->_configurableType->getParentIdsByChild($productId);
        $productIdsToQueue = count($parentIds) > 0 ? $parentIds : [$productId];

        foreach ($productIdsToQueue as $productIdToQueue) {
            if ($this->_productOfferExportQueueItemRepository->isNonExecutedProductPresent($productIdToQueue)) {
                continue;
            }

            // It's possible that products enter this observer that were already deleted from catalog, but references still exists in orders
            try {
                $this->_productRepository->getById($productId);
            } catch (Exception $e) {
                continue;
            }

            $productOfferExportQueueItem = $this->_productOfferExportQueueItemRepository->create();
            $productOfferExportQueueItem->setCatalogProductEntityId($productIdToQueue);
            try {
                $this->_productOfferExportQueueItemRepository->save($productOfferExportQueueItem);
            } catch (CouldNotSaveException $e) {
                continue;
            } catch (Exception $e) {
                continue;
            }
        }
    }

    /**
     * Execute all queued items.
     *
     * @return void
     */
    public function execute()
    {
        $queueSize  = intval($this->_settingsHelper->getOfferExportQueueSize() ?? static::DEFAULT_MAX_QUEUE_ITEMS_PER_EXECUTE);
        $oldest     = $this->_productOfferExportQueueItemRepository->getOldestUnexecuted($queueSize);

        if ($oldest->getTotalCount() === 0) {
            return;
        }

        $products = [];

        /** @var ProductOfferExportQueueItem $productOfferExportQueueItem */
        foreach ($oldest->getItems() as $productOfferExportQueueItem) {
            $productOfferExportQueueItem->setExecutedAt(time());

            try {
                $this->_productOfferExportQueueItemRepository->save($productOfferExportQueueItem);
            } catch (Exception $e) {
                continue;
            }

            $productId = intval($productOfferExportQueueItem->getCatalogProductEntityId());

            try {
                $product    = $this->_productRepository->getById($productId);
                $products[] = $product;
            } catch (Exception $e) {
                continue;
            }
        }

        $this->executeProductsOfferExport($products);
    }

    /**
     * Export products offers.
     *
     * @param ProductInterface[] $products
     * @return void
     */
    protected function executeProductsOfferExport(array $products)
    {
        $exportConnections      = [];
        $connectionProducts     = [];

        foreach ($products as $product) {
            $websiteIds             = array_map(function($websiteId) {
                return intval($websiteId);
            }, $product->getWebsiteIds() ?? []);

            /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
            $searchCriteriaBuilder  = $this->_searchCriteriaBuilderFactory->create();
            $searchCriteria         = $searchCriteriaBuilder
                ->addFilter('is_active', true)
                ->addFilter(
                    'website_id',
                    $websiteIds,
                    'in'
                )->create();

            $connections            = $this->_connectionRepository
                ->getList($searchCriteria)
                ->getItems();

            /** @var Connection $connection */
            foreach ($connections as $connection) {
                $exportConnections[$connection->getEntityId()] = $connection;

                if (!isset($connectionProducts[$connection->getEntityId()])) {
                    $connectionProducts[$connection->getEntityId()] = [];
                }

                $connectionProducts[$connection->getEntityId()][intval($product->getId())] = $product;
            }
        }

        /** @var Connection $connection */
        foreach ($exportConnections as $connection) {
            $connectionId       = $connection->getEntityId();
            $products           = $connectionProducts[$connectionId] ?? [];

            if (count($products) === 0) {
                continue;
            }

            try {
                $instance       = new ConnectionApi($connection, $this->_apiHelper, $this->_logHelper, $this->_settingsHelper);
                $instance->exportSpecificOffers($products);
            } catch (InvalidKeyException $e) {
                $this->_logHelper->logOffersExportConnectionFailed(intval($connectionId), ['exception' => $e->getMessage()]);
                continue;
            }
        }
    }
}