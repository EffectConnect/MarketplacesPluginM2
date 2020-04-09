<?php

namespace EffectConnect\Marketplaces\Helper;

use EffectConnect\Marketplaces\Enums\MsiType;
use EffectConnect\Marketplaces\Enums\QuantityType;
use EffectConnect\Marketplaces\Enums\SalableSourceType;
use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\Context;
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
     * @param SourceRepositoryInterface $sourceRepository
     * @param StockRepositoryInterface $stockRepository
     * @param GetStockSourceLinksInterface $getStockSourceLinks
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param StockResolverInterface $stockResolver
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        StockStateInterface $stockState,
        SettingsHelper $settingsHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceRepositoryInterface $sourceRepository,
        StockRepositoryInterface $stockRepository,
        GetStockSourceLinksInterface $getStockSourceLinks,
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        GetProductSalableQtyInterface $getProductSalableQty,
        StockResolverInterface $stockResolver
    ) {
        parent::__construct($context, $productRepository, $stockState);

        $this->_settingsHelper          = $settingsHelper;
        $this->_searchCriteriaBuilder   = $searchCriteriaBuilder;
        $this->_stockRepository         = $stockRepository;
        $this->_sourceRepository        = $sourceRepository;
        $this->_getStockSourceLinks     = $getStockSourceLinks;
        $this->_getSourceItemsBySku     = $getSourceItemsBySku;
        $this->_getProductSalableQty    = $getProductSalableQty;
        $this->_stockResolver           = $stockResolver;
    }

    /**
     * @param string $productSku
     * @param int $websiteId
     * @return float
     * @throws NoSuchEntityException
     */
    public function getProductStockQuantity(string $productSku, int $websiteId): float
    {
        $stock                          = 0;

        if ($this->traditionalActive($websiteId)) {
            $stock                      = $this->getDefaultStockQuantityBySku($productSku);
        } else {
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
                    $stock              = $this->_getProductSalableQty->execute($productSku, $stockId);
                } catch (Exception $e) {
                    $stock              = 0;
                }
            } else {
                $codes                  = $this->getConfiguredSources($websiteId);
                $stock                  = 0;
                $sourceItemsBySku       = $this->_getSourceItemsBySku->execute($productSku);

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
            !$this->isMsiActive() ||
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
