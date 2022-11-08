<?php

namespace EffectConnect\Marketplaces\Helper;

use EffectConnect\Marketplaces\Enums\MsiType;
use EffectConnect\Marketplaces\Enums\QuantityType;
use EffectConnect\Marketplaces\Enums\SalableSourceType;
use EffectConnect\Marketplaces\Exception\CatalogExportProductHasNoSkuException;
use Exception;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;

/**
 * Class MultiSourceInventoryHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class MultiSourceInventoryHelper extends TraditionalInventoryHelper
{
    /**
     * @var SettingsHelper
     */
    protected $_settingsHelper;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var SourceRepositoryInterface
     */
    protected $_sourceRepository;

    /**
     * @var StockRepositoryInterface
     */
    protected $_stockRepository;

    /**
     * @var GetStockSourceLinksInterface
     */
    protected $_getStockSourceLinks;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    protected $_getSourceItemsBySku;

    /**
     * @var GetProductSalableQtyInterface
     */
    protected $_getProductSalableQty;

    /**
     * @var StockResolverInterface
     */
    protected $_stockResolver;

    /**
     * MultiSourceInventoryHelper constructor.
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param StockStateInterface $stockState
     * @param SettingsHelper $settingsHelper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductLinkManagementInterface $productLinkManagement
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        StockStateInterface $stockState,
        SettingsHelper $settingsHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductLinkManagementInterface $productLinkManagement
    ) {
        parent::__construct($context, $productRepository, $stockState, $productLinkManagement);

        $objectManager                  = ObjectManager::getInstance();

        $this->_settingsHelper          = $settingsHelper;
        $this->_searchCriteriaBuilder   = $searchCriteriaBuilder;

        // Used object manager to create the instances of the dependencies below,
        // because when using dependency injection, setup:di:compile command fails
        // when the Magento Inventory modules are excluded in the composer.json.
        $this->_sourceRepository        = $objectManager->create(SourceRepositoryInterface::class);
        $this->_stockRepository         = $objectManager->create(StockRepositoryInterface::class);
        $this->_getStockSourceLinks     = $objectManager->create(GetStockSourceLinksInterface::class);
        $this->_getSourceItemsBySku     = $objectManager->create(GetSourceItemsBySkuInterface::class);
        $this->_getProductSalableQty    = $objectManager->create(GetProductSalableQtyInterface::class);
        $this->_stockResolver           = $objectManager->create(StockResolverInterface::class);
    }

    /**
     * @param ProductInterface $product
     * @param int $websiteId
     * @return float
     * @throws CatalogExportProductHasNoSkuException
     */
    public function getProductStockQuantity(ProductInterface $product, int $websiteId): float
    {
        if ($product->getTypeId() === Type::TYPE_BUNDLE) {
            return $this->getBundleStockQuantity($product, $websiteId);
        }

        $stock                          = 0;

        if ($this->traditionalActive($websiteId)) {
            $stock                      = $this->getDefaultStockQuantity($product);
        } else {
            $sku = $product->getSku();

            if (is_null($sku) || empty($sku) || !is_string($sku)) {
                throw new CatalogExportProductHasNoSkuException(__('Product with ID %1 does not have a SKU and will therefor not be included in the catalog export.', $product->getId()));
            }

            $quantityType               = strval($this->_settingsHelper->getOfferExportQuantityType(SettingsHelper::SCOPE_WEBSITE, $websiteId) ?? QuantityType::SALABLE()->getValue());

            if ($quantityType === QuantityType::SALABLE()->getValue()) {
                $salableSourceType      = strval($this->_settingsHelper->getOfferExportSalableSource(SettingsHelper::SCOPE_WEBSITE, $websiteId) ?? SalableSourceType::WEBSITE()->getValue());
                $stockId                = 1;

                if ($salableSourceType  === SalableSourceType::WEBSITE()->getValue()) {
                    $websiteCode        = strval($this->_settingsHelper->getOfferExportWebsite(SettingsHelper::SCOPE_WEBSITE, $websiteId));

                    try {
                        $stockId        = $websiteCode ? $this->_stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId() : 1;
                    } catch (NoSuchEntityException $e) {
                        $stockId        = 1;
                    }
                } else {
                    $stockId            = intval($this->_settingsHelper->getOfferExportStock(SettingsHelper::SCOPE_WEBSITE, $websiteId) ?? 1);
                }

                try {
                    $stock              = $this->_getProductSalableQty->execute($sku, $stockId);
                } catch (Exception $e) {
                    $stock              = 0;
                }
            } else {
                $codes                  = $this->getConfiguredSources($websiteId);
                $stock                  = 0;
                $sourceItemsBySku       = $this->_getSourceItemsBySku->execute($sku);

                foreach ($sourceItemsBySku as $sourceItem) {
                    if (in_array($sourceItem->getSourceCode(), $codes)) {
                        $stock         += $sourceItem->getQuantity() ?? 0;
                    }
                }
            }
        }

        return $stock;
    }

    /**
     * @param int $websiteId
     * @return array
     */
    public function getConfiguredSources(int $websiteId): array
    {
        $sources                        = [];

        if (!$this->isMsiActive()) {
            return $sources;
        }

        switch (strval($this->_settingsHelper->getOfferExportMsiType(SettingsHelper::SCOPE_WEBSITE, $websiteId))) {
            case MsiType::STOCKS()->getValue():
                $values                 = $this->getConfiguredStocks($websiteId);

                foreach ($values as $stockId) {
                    $sources            = array_unique(array_merge($sources, $this->getSourceCodesFromStockId($stockId)));
                }

                break;
            case MsiType::SOURCES()->getValue():
                $value                  = strval($this->_settingsHelper->getOfferExportSources(SettingsHelper::SCOPE_WEBSITE, $websiteId));
                $values                 = !is_null($value) && !empty($value) ? explode(',', $value) : [];
                $sources                = array_unique(array_merge($sources, $values));

                break;
            default:
                break;
        }

        return $this->filterDisabledSources($sources);
    }

    /**
     * @param int $websiteId
     * @return array
     */
    public function getConfiguredStocks(int $websiteId): array
    {
        $stocks                         = [];

        if (!$this->isMsiActive()) {
            return $stocks;
        }

        $value                          = strval($this->_settingsHelper->getOfferExportStocks(SettingsHelper::SCOPE_WEBSITE, $websiteId));
        $stocks                         = !is_null($value) && !empty($value) ? explode(',', $value) : [];

        return $stocks;
    }

    /**
     * @return array
     */
    public function getSourceOptions() : array
    {
        $options                        = [];

        if (!$this->isMsiActive()) {
            return $options;
        }

        $sourceCollection               = $this->_sourceRepository->getList();
        $sources                        = $sourceCollection->getItems();

        foreach ($sources as $source) {
            if (!$source->isEnabled()) {
                continue;
            }

            $sourceCode                 = $source->getSourceCode();
            $sourceName                 = $source->getName() . ' (' . $sourceCode . ')';

            if ($sourceCode === 'default') {
                $sourceName            .= ' [' . __('default') . ']';
            }

            $options[]                  = [
                'value'                 => $sourceCode,
                'label'                 => $sourceName
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getStockOptions() : array
    {
        $options                        = [];

        if (!$this->isMsiActive()) {
            return $options;
        }

        $stockCollection                = $this->_stockRepository->getList();
        $stocks                         = $stockCollection->getItems();

        foreach ($stocks as $stock) {
            $stockId                    = $stock->getStockId();
            $stockName                  = $stock->getName();

            if ($stockId === 1) {
                $stockName             .= ' (' . __('default') . ')';
            }

            $options[]                  = [
                'value'                 => $stockId,
                'label'                 => $stockName
            ];
        }

        return $options;
    }

    /**
     * @param int $websiteId
     * @return bool
     */
    public function traditionalActive(int $websiteId) : bool
    {
        return
            !$this->isMsiAvailable() ||
            !$this->isMsiEnabled() ||
            $this->_settingsHelper->getOfferExportQuantityType(SettingsHelper::SCOPE_WEBSITE, $websiteId) === QuantityType::TRADITIONAL()->getValue();
    }

    /**
     * @return bool
     */
    public function isMsiActive() : bool
    {
        return
            $this->isMsiAvailable() &&
            $this->isMsiEnabled() &&
            $this->isMsiUsed();
    }

    /**
     * @return bool
     */
    protected function isMsiUsed() : bool
    {
        $sourceItems                = $this->_sourceRepository->getList()->getItems();
        $activeSources              = count(array_filter($sourceItems, function ($source) {
            /** @var SourceInterface $source */
            return $source->isEnabled();
        }));
        $anySourceActive            = $activeSources > 1;

        return $anySourceActive;
    }


    /**
     * @param int $stockId
     * @return array
     */
    protected function getSourceCodesFromStockId(int $stockId) : array
    {
        $searchCriteria             = $this->_searchCriteriaBuilder
            ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId)
            ->create();

        $sourceLinks                = $this->_getStockSourceLinks
            ->execute($searchCriteria)
            ->getItems();

        return array_map(function (StockSourceLinkInterface $item) {
            return $item->getSourceCode();
        }, $sourceLinks);
    }

    /**
     * @param array $sourceCodes
     * @return array
     */
    protected function filterDisabledSources(array $sourceCodes) : array
    {
        return array_filter($sourceCodes, function (string $code) {
            $source                 = $this->_sourceRepository->get($code);
            $enabled                = $source ? $source->isEnabled() : false;

            return $enabled;
        });
    }
}
