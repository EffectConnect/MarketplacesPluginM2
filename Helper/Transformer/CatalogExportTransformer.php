<?php

namespace EffectConnect\Marketplaces\Helper\Transformer;

use EffectConnect\Marketplaces\Enums\LogCode;
use EffectConnect\Marketplaces\Exception\CatalogExportEanNotValidException;
use EffectConnect\Marketplaces\Exception\CatalogExportObligatedAttributeIsNullException;
use EffectConnect\Marketplaces\Exception\CatalogExportProductHasNoSkuException;
use EffectConnect\Marketplaces\Helper\InventoryHelper;
use EffectConnect\Marketplaces\Helper\LogHelper;
use EffectConnect\Marketplaces\Helper\SettingsHelper;
use EffectConnect\Marketplaces\Helper\XmlGenerator;
use EffectConnect\Marketplaces\Interfaces\ValueType;
use DOMException;
use EffectConnect\Marketplaces\Model\Connection;
use Exception;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend\Filter\Word\SeparatorToCamelCase;
use Zend\Filter\Word\UnderscoreToSeparator;
use Zend\Validator\Barcode;

/**
 * The CatalogExportTransformer obtains the catalog from a certain website in the Magento 2 installation.
 * Then it transforms that catalog into a format that can be used with the EffectConnect Marketplaces SDK.
 *
 * Class CatalogExportTransformer
 * @package EffectConnect\Marketplaces\Helper\Transformer
 */
class CatalogExportTransformer extends AbstractHelper implements ValueType
{
    /**
     * The default products per page when obtaining the catalog.
     */
    const DEFAULT_PAGE_SIZE     = 50;

    /**
     * The maximum amount of images per product.
     */
    const MAXIMUM_IMAGES_AMOUNT = 10;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $_categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var SettingsHelper
     */
    protected $_settingsHelper;

    /**
     * @var InventoryHelper
     */
    protected $_inventoryHelper;

    /**
     * @var LogHelper
     */
    protected $_logHelper;

    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * @var Data
     */
    protected $_taxHelper;

    /**
     * @var Configurable
     */
    protected $_configurableType;

    /**
     * @var FileSystem
     */
    protected $_filesystem;

    /**
     * @var DirectoryList
     */
    protected $_directoryList;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilderFactory;

    /**
     * @var Status
     */
    protected $_productStatus;

    /**
     * @var Visibility
     */
    protected $_productVisibility;

    /**
     * @var Config
     */
    protected $_productMediaConfig;

    /**
     * @var array
     */
    protected $_storeViewMapping;

    /**
     * @var Connection
     */
    protected $_connection;

    /**
     * @var array
     */
    protected $_defaultAttributes;


    /**
     * Constructs the CatalogExportTransformer helper class.
     *
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param SettingsHelper $settingsHelper
     * @param InventoryHelper $inventoryHelper
     * @param LogHelper $logHelper
     * @param Image $imageHelper
     * @param Data $taxHelper
     * @param Configurable $configurableType
     * @param Filesystem $filesystem
     * @param DirectoryList $directoryList
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param Status $productStatus
     * @param Visibility $productVisibility
     * @param Config $productMediaConfig
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        SettingsHelper $settingsHelper,
        InventoryHelper $inventoryHelper,
        LogHelper $logHelper,
        Image $imageHelper,
        Data $taxHelper,
        Configurable $configurableType,
        Filesystem $filesystem,
        DirectoryList $directoryList,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        Status $productStatus,
        Visibility $productVisibility,
        Config $productMediaConfig
    ) {
        parent::__construct($context);
        $this->_productRepository               = $productRepository;
        $this->_categoryRepository              = $categoryRepository;
        $this->_storeManager                    = $storeManager;
        $this->_settingsHelper                  = $settingsHelper;
        $this->_inventoryHelper                 = $inventoryHelper;
        $this->_logHelper                       = $logHelper;
        $this->_imageHelper                     = $imageHelper;
        $this->_taxHelper                       = $taxHelper;
        $this->_configurableType                = $configurableType;
        $this->_filesystem                      = $filesystem;
        $this->_directoryList                   = $directoryList;
        $this->_searchCriteriaBuilderFactory    = $searchCriteriaBuilderFactory;
        $this->_productStatus                   = $productStatus;
        $this->_productVisibility               = $productVisibility;
        $this->_productMediaConfig              = $productMediaConfig;
        $this->_storeViewMapping                = [];
        $this->_defaultAttributes               = [];
    }


    /**
     * Get the catalog, transformed to the EffectConnect Marketplaces SDK expected format as an array.
     *
     * @param Connection $connection
     * @return array
     */
    public function get(Connection $connection) : array
    {
        $this->_connection  = $connection;

        $this->setStoreViewMapping();

        return $this->transformCatalog(
            $this->getWebsiteCatalog()
        );
    }

    /**
     * Get the catalog, transformed to the EffectConnect Marketplaces SDK expected format as an XML string.
     *
     * @param Connection $connection
     * @return string
     */
    public function getXml(Connection $connection) : string
    {
        try {
            return strval(XmlGenerator::convert(
                    $this->get($connection),
                    [
                        'rootElementName' => 'products'
                    ],
                    true,
                    'utf-8',
                    '1.0',
                    [],
                    true
                ) ?? '');
        } catch (DOMException $e) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_HAS_ENDED(), [
                intval($connection->getEntityId()),
                false,
                [
                    'error' => $e->getMessage()
                ]
            ]);
        }
    }

    /**
     * Save catalog as XML to an XML file.
     *
     * @param Connection $connection
     * @param string $exportFileName
     * @return bool|string
     */
    public function saveXml(Connection $connection, string $exportFileName = 'catalog')
    {
        $catalog = $this->get($connection);

        $directoryType  = 'var';
        $relativeFile   = 'effectconnect/marketplaces/export/' . $exportFileName . '.xml';

        try {
            $fileLocation   = $this->_directoryList->getPath($directoryType) . '/' . $relativeFile;
        } catch (FileSystemException $e) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_FILE_CREATION_FAILED(), [
                intval($connection->getEntityId()),
                '-'
            ]);
            return false;
        }

        try {
            $transaction = XmlGenerator::startMageStorageTransaction($this->_filesystem, $directoryType, $relativeFile, 'products');
        } catch (DOMException $e) {
            $transaction = false;
        } catch (FileSystemException $e) {
            $transaction = false;
        }

        if (!$transaction) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_FILE_CREATION_FAILED(), [
                intval($connection->getEntityId()),
                $fileLocation
            ]);
            return false;
        }

        foreach ($catalog['product'] as $product) {
            $success = false;
            try {
                if ($transaction->appendToMageStorageFile($product, 'product')) {
                    $success = true;
                }
            } catch (DOMException $e) {
                $success = false;
            }

            if (!$success) {
                $this->writeToLog(LogCode::CATALOG_EXPORT_FILE_CREATION_FAILED(), [
                    intval($connection->getEntityId()),
                    intval($product['identifier']) ?? 0
                ]);
                return false;
            }
        }

        if (!$transaction->endMageStorageTransaction()) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_FILE_CREATION_FAILED(), [
                intval($connection->getEntityId()),
                $fileLocation
            ]);
            return false;
        }

        return $fileLocation;
    }

    /**
     * Transform and write catalog to EffectConnect Marketplaces desired XML format (segmented per product).
     *
     * @param Connection $connection
     * @param string $exportFileName
     * @param ProductInterface[] $catalog
     * @return bool|string
     */
    public function saveXmlSegmented(Connection $connection, string $exportFileName = 'catalog', array $catalog = null)
    {
        $this->_connection  = $connection;

        $this->setStoreViewMapping();

        if (count($this->_storeViewMapping) === 0) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_NO_STOREVIEW_MAPPING_DEFINED(), [
                intval($connection->getEntityId())
            ]);
            return false;
        }

        $eans               = [];
        $directoryType      = 'var';
        $relativeFile       = 'effectconnect/marketplaces/export/' . $exportFileName . '.xml';

        try {
            $fileLocation   = $this->_directoryList->getPath($directoryType) . '/' . $relativeFile;
        } catch (FileSystemException $e) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_FILE_CREATION_FAILED(), [
                intval($connection->getEntityId()),
                '-'
            ]);
            return false;
        }

        try {
            $transaction    = XmlGenerator::startMageStorageTransaction($this->_filesystem, $directoryType, $relativeFile, 'products');
        } catch (DOMException $e) {
            $transaction    = false;
        } catch (FileSystemException $e) {
            $transaction    = false;
        }

        if (!$transaction) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_FILE_CREATION_FAILED(), [
                intval($connection->getEntityId()),
                $fileLocation
            ]);

            return false;
        }

        if (is_null($catalog)) {
            $lastPage       = true;
            $currentPage    = 1;
            $itemsPerPage   = $this->getPageSize();

            do {
                $catalog = $this->getWebsiteCatalog($itemsPerPage, $currentPage, $lastPage);

                /** @var ProductInterface $product */
                foreach ($catalog as $product) {
                    if ($product instanceof ProductInterface) {
                        $this->saveProductXml($product, $connection, $transaction, $eans);
                    }
                }

                $currentPage++;
            } while ($lastPage === false);
        } else {
            /** @var ProductInterface $product */
            foreach ($catalog as $product) {
                if ($product instanceof ProductInterface) {
                    $this->saveProductXml($product, $connection, $transaction, $eans);
                }
            }
        }

        if (!$transaction->endMageStorageTransaction()) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_FILE_CREATION_FAILED(), [
                intval($connection->getEntityId()),
                $fileLocation
            ]);
            return false;
        }

        return $fileLocation;
    }

    /**
     * Transform and write product to EffectConnect Marketplaces desired XML format.
     *
     * @param ProductInterface $product
     * @param Connection $connection
     * @param XmlGenerator $transaction
     * @param array $eans
     * @return void
     */
    protected function saveProductXml(ProductInterface $product, Connection $connection, XmlGenerator &$transaction, array &$eans)
    {
        $parentIds      = $this->_configurableType->getParentIdsByChild($product->getId());

        if (!empty($parentIds)) {
            // Skip simple products if they are linked to a configurable product.
            // When the configurable product is transformed, the simple products are added with it.
            return;
        }

        if (!$this->checkProductShouldBeExported($product)) {
            return;
        }

        $transformed    = $this->transformProduct($product);

        if (is_null($transformed)) {
            return;
        }

        if ($this->checkForDuplicateEans() === true) {
            $options = isset($transformed['options']) ? ($transformed['options']['option'] ?? []) : [];
            foreach ($options as $index => $option) {
                $ean        = $option['ean'];

                if (in_array($ean, $eans)) {
                    $this->writeToLog(LogCode::CATALOG_EXPORT_EAN_ALREADY_IN_USE(), [
                        intval($this->_connection->getEntityId()),
                        isset($option['identifier']['_cdata']) ? intval($option['identifier']['_cdata']) : 0,
                        $ean
                    ]);
                    unset($transformed['options']['option'][$index]);
                    continue;
                }

                $eans[]     = $ean;
            }

            if (isset($transformed['options']['option']) && count($transformed['options']['option']) === 0) {
                unset($transformed['options']['option']);
            }
        }

        if (empty($transformed['options'])) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_PRODUCT_HAS_NO_VALID_OPTIONS(), [
                intval($this->_connection->getEntityId()),
                isset($transformed['identifier']['_cdata']) ? intval($transformed['identifier']['_cdata']) : 0
            ]);

            return;
        }

        $success = false;

        try {
            if ($transaction->appendToMageStorageFile($transformed, 'product')) {
                $success = true;
            }
        } catch (DOMException $e) {
            $success = false;
        }

        if (!$success) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_FILE_CREATION_FAILED(), [
                intval($connection->getEntityId()),
                intval($product['identifier']) ?? 0
            ]);
            return;
        }
    }


    /**
     * Set the store view mapping based on the connection configuration.
     *
     * @return void
     */
    protected function setStoreViewMapping()
    {
        $this->_storeViewMapping    = [];

        foreach ($this->_connection->getStoreViews() as $storeView) {
            if (empty($storeView->getLanguageCode())) {
                continue;
            }

            $this->_storeViewMapping[$storeView->getLanguageCode()] = intval($storeView->getStoreviewId());
        }
    }


    /**
     * Transform the catalog products to the EffectConnect Marketplaces SDK expected format.
     *
     * @param array $products
     * @return array
     */
    protected function transformCatalog(array $products) : array
    {
        $transformedProducts = [];

        /** @var ProductInterface $product */
        foreach ($products as $product) {
            if ($product instanceof ProductInterface) {
                $parentIds                  = $this->_configurableType->getParentIdsByChild($product->getId());

                if (!empty($parentIds)) {
                    // Skip simple products if they are linked to a configurable product.
                    // When the configurable product is transformed, the simple products are added with it.
                    continue;
                }

                if (!$this->checkProductShouldBeExported($product)) {
                    continue;
                }

                $transformed                = $this->transformProduct($product);

                if (!is_null($transformed)) {
                    $transformedProducts[]  = $transformed;
                }
            }
        }

        if ($this->checkForDuplicateEans() === true) {
            $eans = [];

            foreach ($transformedProducts as $productIndex => $product) {
                $options = isset($product['options']) ? ($product['options']['option'] ?? []) : [];
                foreach ($options as $index => $option) {
                    $ean = $option['ean'];
                    if (in_array($ean, $eans)) {
                        $this->writeToLog(LogCode::CATALOG_EXPORT_EAN_ALREADY_IN_USE(), [
                            intval($this->_connection->getEntityId()),
                            isset($option['identifier']['_cdata']) ? intval($option['identifier']['_cdata']) : 0,
                            $ean
                        ]);
                        unset($product['options']['option'][$index]);
                        continue;
                    }
                    $eans[] = $ean;
                }

                if (isset($transformedProducts[$productIndex]['options']['option']) && count($transformedProducts[$productIndex]['options']['option']) === 0) {
                    unset($transformedProducts[$productIndex]['options']['option']);
                }

                if (empty($transformedProducts[$productIndex]['options'])) {
                    $this->writeToLog(LogCode::CATALOG_EXPORT_PRODUCT_HAS_NO_VALID_OPTIONS(), [
                        intval($this->_connection->getEntityId()),
                        isset($transformedProducts[$productIndex]['identifier']['_cdata']) ? intval($transformedProducts[$productIndex]['identifier']['_cdata']) : 0
                    ]);

                    continue;
                }
            }
        }

        return [
            'product'                       => $transformedProducts
        ];
    }

    /**
     * Transform a certain product to the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return array|null
     */
    protected function transformProduct(ProductInterface $product)
    {
        try {
            $product = $this->_productRepository->getById($product->getId(), false, 0);
        } catch (NoSuchEntityException $e) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_PRODUCT_NOT_FOUND(), [
                intval($this->_connection->getEntityId()),
                intval($product->getId())
            ]);
            return null;
        }

        if (!($product instanceof ProductInterface)) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_PRODUCT_NOT_FOUND(), [
                intval($this->_connection->getEntityId()),
                intval($product->getId())
            ]);
            return null;
        }

        $identifier     = $this->getProductIdentifier($product);
        $brand          = $this->getProductBrand($product);
        $categories     = $this->getProductCategories($product);
        $options        = $this->getProductOptions($product);

        $transformed    = [];

        try {
            $this->setValueToArray($transformed, 'identifier', $identifier, true);
            $this->setValueToArray($transformed, 'brand', $brand);
            $this->setValueToArray($transformed, 'categories', $categories);
            $this->setValueToArray($transformed, 'options', $options, true, []);
        } catch (CatalogExportObligatedAttributeIsNullException $e) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET(), [
                intval($this->_connection->getEntityId()),
                intval($identifier),
                strval($e->getParameters()[0] ?? '')
            ]);
            return null;
        }

        if (empty($transformed['options'])) {
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $this->writeToLog(LogCode::CATALOG_EXPORT_PRODUCT_HAS_NO_VALID_OPTIONS(), [
                    intval($this->_connection->getEntityId()),
                    intval($identifier)
                ]);
            }

            return null;
        }

        return $transformed;
    }

    /**
     * Transform a certain product option to the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $productOption
     * @return array|null
     */
    protected function transformProductOption(ProductInterface $productOption)
    {
        $identifier     = $this->getProductIdentifier($productOption);
        $cost           = $this->getProductCost($productOption);
        $price          = $this->getProductPrice($productOption);
        $priceOriginal  = $this->getProductPriceOriginal($productOption);
        $stock          = $this->getProductStock($productOption);
        $titles         = $this->getProductTitles($productOption);
        $urls           = $this->getProductUrls($productOption);
        $descriptions   = $this->getProductDescriptions($productOption);
        $sku            = $this->getProductSku($productOption);
        $deliveryTime   = $this->getProductDeliveryTime($productOption);
        $images         = $this->getProductImages($productOption);
        $attributes     = $this->getProductAttributes($productOption);

        $transformed    = [];

        $exportEan      = boolval($this->_settingsHelper->getCatalogExportUseAndValidateEan(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId())));
        if ($exportEan)
        {
            try {
                $ean = $this->getProductEan($productOption);
                $this->setValueToArray($transformed, 'ean', $ean, true, null, false);
            } catch (CatalogExportEanNotValidException $e) {
                $this->writeToLog(LogCode::CATALOG_EXPORT_EAN_NOT_VALID(), [
                    intval($this->_connection->getEntityId()),
                    intval($identifier),
                    strval($e->getParameters()[1] ?? '')
                ]);
                return null;
            }
        }

        try {
            $this->setValueToArray($transformed, 'identifier', $identifier, true);
            $this->setValueToArray($transformed, 'cost', $cost);
            $this->setValueToArray($transformed, 'price', $price, true);
            $this->setValueToArray($transformed, 'priceOriginal', $priceOriginal);
            $this->setValueToArray($transformed, 'stock', $stock);
            $this->setValueToArray($transformed, 'titles', $titles, true, []);
            $this->setValueToArray($transformed, 'urls', $urls);
            $this->setValueToArray($transformed, 'descriptions', $descriptions);
            $this->setValueToArray($transformed, 'sku', $sku, true, '');
            $this->setValueToArray($transformed, 'deliveryTime', $deliveryTime);
            $this->setValueToArray($transformed, 'images', $images);
            $this->setValueToArray($transformed, 'attributes', $attributes);
        } catch (CatalogExportObligatedAttributeIsNullException $e) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET(), [
                intval($this->_connection->getEntityId()),
                intval($identifier),
                strval($e->getParameters()[0] ?? '')
            ]);
            return null;
        }

        return $transformed;
    }


    /**
     * Check whether a product should be exported.
     * When for example the product is a virtual product or is disabled,
     * the product does not have to be exported.
     *
     * @param ProductInterface $product
     * @return bool
     */
    protected function checkProductShouldBeExported(ProductInterface $product) : bool
    {
        switch(true) {
            case is_null($product->getStatus()) || !in_array($product->getStatus(), $this->_productStatus->getVisibleStatusIds()):
                $this->writeToLog(LogCode::CATALOG_EXPORT_PRODUCT_NOT_ENABLED(), [
                    intval($this->_connection->getEntityId()),
                    intval($product->getId())
                ]);
                return false;
            case is_null($product->getTypeId()) || ($product->getTypeId() !== Configurable::TYPE_CODE && $product->getTypeId() !== Type::TYPE_SIMPLE):
                $this->writeToLog(LogCode::CATALOG_EXPORT_PRODUCT_TYPE_NOT_SUPPORTED(), [
                    intval($this->_connection->getEntityId()),
                    intval($product->getId()),
                    $product->getTypeId() ?? 'unknown'
                ]);
                return false;
            case is_null($product->getVisibility()) || $product->getVisibility() === Visibility::VISIBILITY_NOT_VISIBLE:
                $this->writeToLog(LogCode::CATALOG_EXPORT_PRODUCT_NOT_VISIBLE(), [
                    intval($this->_connection->getEntityId()),
                    intval($product->getId())
                ]);
                return false;
            default:
                return true;
        }
    }


    /**
     * Get the product's identifier in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return string
     */
    protected function getProductIdentifier(ProductInterface $product) : string
    {
        $this->_defaultAttributes[] = 'entity_id';
        return strval($product->getId());
    }

    /**
     * Get the product's brand in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return string|null
     */
    protected function getProductBrand(ProductInterface $product)
    {
        $attributeCode              = $this->_settingsHelper
            ->getCatalogExportBrandAttribute(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId()));

        $this->_defaultAttributes[] = $attributeCode;

        return $this->getProductAttribute($product, $attributeCode, static::VALUE_TYPE_STRING, 64);
    }

    /**
     * Get the product's cost in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return float|null
     */
    protected function getProductCost(ProductInterface $product)
    {
        $attributeCode              = $this->_settingsHelper
            ->getCatalogExportCostAttribute(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId()));

        $this->_defaultAttributes[] = $attributeCode;

        $cost                       = $this->getProductAttribute($product, $attributeCode, static::VALUE_TYPE_FLOAT);

        return !is_null($cost) ? $this->_taxHelper->getTaxPrice($product, $cost, true) : null;
    }

    /**
     * Get the product's price in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return float|null
     */
    protected function getProductPrice(ProductInterface $product)
    {
        $priceAttributeCode             = $this->_settingsHelper
            ->getCatalogExportPriceAttribute(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId()));

        $this->_defaultAttributes[]     = $priceAttributeCode;

        $useSpecialPrice                = boolval($this->_settingsHelper
            ->getCatalogExportUseSpecialPrice(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId())));

        $priceFallback                  = boolval($this->_settingsHelper
            ->getCatalogExportPriceFallback(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId())));

        $price = $this->getProductAttribute($product, $priceAttributeCode, static::VALUE_TYPE_FLOAT);

        if ($useSpecialPrice) {
            $this->_defaultAttributes[] = 'special_price';
            $specialPrice               = $this->getProductAttribute($product, 'special_price', static::VALUE_TYPE_FLOAT);
            $price                      = $specialPrice ?? $price;
        }

        if ($priceFallback && empty($price)) {
            $this->_defaultAttributes[] = 'price';
            $price                      = $this->getProductAttribute($product, 'price', static::VALUE_TYPE_FLOAT);
        }

        return !empty($price) ? $this->_taxHelper->getTaxPrice($product, $price, true) : null;
    }

    /**
     * Get the product's original price in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return float|null
     */
    protected function getProductPriceOriginal(ProductInterface $product)
    {
        $useSpecialPrice    = boolval($this->_settingsHelper
            ->getCatalogExportUseSpecialPrice(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId())));

        if (!$useSpecialPrice) {
            return null;
        }

        if (empty($this->getProductAttribute($product, 'special_price', static::VALUE_TYPE_FLOAT))) {
            return null;
        }

        $priceFallback      = boolval($this->_settingsHelper
            ->getCatalogExportPriceFallback(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId())));

        $priceAttributeCode = $this->_settingsHelper
            ->getCatalogExportPriceAttribute(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId()));

        $price              = $this->getProductAttribute($product, $priceAttributeCode, static::VALUE_TYPE_FLOAT);

        if ($priceFallback && empty($price)) {
            $price          = $this->getProductAttribute($product, 'price', static::VALUE_TYPE_FLOAT);
        }

        return !empty($price) ? $this->_taxHelper->getTaxPrice($product, $price, true) : null;
    }

    /**
     * Get the product's price in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return int|null
     */
    protected function getProductStock(ProductInterface $product)
    {
        $stockTracking = boolval($this->_settingsHelper->getOfferExportStockTracking(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId())));

        if (!$stockTracking) {
            $fictionalStock = intval($this->_settingsHelper->getOfferExportFictionalStock(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId())) ?? 0);
            return $fictionalStock;
        }

        try {
            $stock = intval($this->_inventoryHelper->getProductStockQuantity($product, intval($this->_connection->getWebsiteId())));
            switch (true) {
                case $stock < 0: return 0;
                case $stock > 9999: return 9999;
                default: return $stock;
            }
        } catch (CatalogExportProductHasNoSkuException $e) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_PRODUCT_HAS_NO_SKU(), [
                intval($this->_connection->getEntityId()),
                intval($product->getId())
            ]);
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get the product's title in all mapped languages in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return array|null
     */
    protected function getProductTitles(ProductInterface $product)
    {
        $translated = [
            'title' => []
        ];

        foreach ($this->_storeViewMapping as $language => $storeViewId) {
            $attributeCode  = $this->_settingsHelper
                ->getCatalogExportTitleAttribute(SettingsHelper::SCOPE_STORE, $storeViewId);

            try {
                $product    = $this->_productRepository->getById($product->getId(), false, $storeViewId);
            } catch (NoSuchEntityException $e) {
                continue;
            }

            if (!($product instanceof ProductInterface)) {
                continue;
            }

            $title                  =  $this->getProductAttribute($product, $attributeCode, static::VALUE_TYPE_STRING);

            if (empty($title)) {
                $title              = '-';
            }

            $translated['title'][]  =  [
                '_cdata'            => $title,
                '_attributes'       => [
                    'language'      => strval($language)
                ]
            ];
        }

        return count($translated['title']) > 0 ? $translated : null;
    }

    /**
     * Get the product's URL in all mapped languages in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return array|null
     */
    protected function getProductUrls(ProductInterface $product)
    {
        $translated = [
            'url'   => []
        ];

        foreach ($this->_storeViewMapping as $language => $storeViewId) {
            try {
                $product    = $this->_productRepository->getById($product->getId(), false, $storeViewId);
            } catch (NoSuchEntityException $e) {
                continue;
            }

            if (!($product instanceof ProductInterface)) {
                continue;
            }

            $url                    =  strval($product->getProductUrl());

            if (empty($url)) {
                continue;
            }

            $translated['url'][]    =  [
                '_cdata'            => $url,
                '_attributes'       => [
                    'language'      => strval($language)
                ]
            ];
        }

        return count($translated['url']) > 0 ? $translated : null;
    }

    /**
     * Get the product's description in all mapped languages in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return array|null
     */
    protected function getProductDescriptions(ProductInterface $product)
    {
        $translated         = [
            'description'   => []
        ];

        foreach ($this->_storeViewMapping as $language => $storeViewId) {
            $attributeCode  = $this->_settingsHelper
                ->getCatalogExportDescriptionAttribute(SettingsHelper::SCOPE_STORE, $storeViewId);

            try {
                $product    = $this->_productRepository->getById($product->getId(), false, $storeViewId);
            } catch (NoSuchEntityException $e) {
                continue;
            }

            if (!($product instanceof ProductInterface)) {
                continue;
            }

            $description                    =  $this->getProductAttribute($product, $attributeCode, static::VALUE_TYPE_STRING);

            if (empty($description)) {
                continue;
            }

            $translated['description'][]    =  [
                '_cdata'                    => $description,
                '_attributes'               => [
                    'language'              => strval($language)
                ]
            ];
        }

        return count($translated['description']) > 0 ? $translated : null;
    }

    /**
     * Get the product's EAN (European Article Number) in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return mixed|null
     * @throws CatalogExportEanNotValidException
     */
    protected function getProductEan(ProductInterface $product)
    {
        $attributeCode              = $this->_settingsHelper
            ->getCatalogExportEanAttribute(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId()));

        $this->_defaultAttributes[] = $attributeCode;

        $ean                        = $this->getProductAttribute($product, $attributeCode, static::VALUE_TYPE_STRING, 13);

        $valid                      = (new Barcode('EAN13'))->isValid($ean);

        if (!$valid) {
            throw new CatalogExportEanNotValidException(__('Product with ID %1 does not have a valid EAN (%2) and will therefor not be included in the catalog export.', $product->getId(), $ean));
        }

        return $valid ? $ean : null;
    }

    /**
     * Get the product's SKU (Stock Keeping Unit) in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return string|null
     */
    protected function getProductSku(ProductInterface $product)
    {
        $this->_defaultAttributes[] = 'sku';
        return $this->getProductAttribute($product, 'sku', static::VALUE_TYPE_STRING, 64);
    }

    /**
     * Get the product's delivery time in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return string|null
     */
    protected function getProductDeliveryTime(ProductInterface $product)
    {
        $attributeCode              = $this->_settingsHelper
            ->getCatalogExportDeliveryTimeAttribute(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId()));

        $this->_defaultAttributes[] = $attributeCode;

        return $this->getProductAttribute($product, $attributeCode, static::VALUE_TYPE_STRING, 128);
    }

    /**
     * Get the product's images in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return array|null
     */
    protected function getProductImages(ProductInterface $product)
    {
        $this->_defaultAttributes[] = 'images';

        try {
            $storeView = $this->_storeManager->getStore(intval($this->_connection->getImageUrlStoreviewId()));
        } catch (NoSuchEntityException $e) {
            try {
                $storeView = $this->_storeManager->getStore();
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }

        $baseUrl    = $storeView->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $this->_productMediaConfig->getBaseMediaPath();
        $media      = $product->getMediaGalleryImages()->getItems();
        $images     = [
            'image' => []
        ];

        usort($media, function ($itemA, $itemB) {
            return intval($itemA->getPosition()) <=> intval($itemB->getPosition());
        });

        $media      = array_filter($media, function ($item) {
            return !boolval($item->getDisabled()) && strval($item->getMediaType()) === IMAGES::CODE_IMAGE;
        });

        $counter    = 0;
        foreach ($media as $item) {
            $counter++;

            if ($counter > static::MAXIMUM_IMAGES_AMOUNT) {
                continue;
            }

            $file   = $item->getFile();
            $path   = $item->getPath();
            $size   = file_exists($path) ? intval(filesize($path)) : null;
            $md5    = file_exists($path) ? md5_file($path) : null;
            $data   = [];

            try {
                $this->setValueToArray($data, 'url', $baseUrl . $file, true, '');
                $this->setValueToArray($data, 'size', $size);
                if (strlen($md5) <= 32) {
                    $this->setValueToArray($data, 'md5checksum', $md5, false, null, false);
                }
                $this->setValueToArray($data, 'order', intval($item->getPosition()), true, 0);
            } catch (CatalogExportObligatedAttributeIsNullException $e) {
                $this->writeToLog(LogCode::CATALOG_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET(), [
                    intval($this->_connection->getEntityId()),
                    intval($product->getId() ?? 0),
                    strval($e->getParameters()[0] ?? '')
                ]);
                return null;
            }

            $images['image'][]  = $data;
        }

        if ($counter > static::MAXIMUM_IMAGES_AMOUNT) {
            $this->writeToLog(LogCode::CATALOG_EXPORT_MAXIMUM_IMAGES_EXCEEDED(), [
                intval($this->_connection->getEntityId()),
                intval($product->getId() ?? 0),
                static::MAXIMUM_IMAGES_AMOUNT,
                $counter
            ]);
        }

        return count($images['image']) > 0 ? $images : null;
    }

    /**
     * Get all other product's attributes in all mapped languages in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return array|null
     */
    protected function getProductAttributes(ProductInterface $product)
    {
        $attributes     = [
            'attribute' => []
        ];

        foreach ($product->getAttributes() as $code => $attribute) {
            $this->getProductAttributeData($attributes['attribute'], $product, $code, $attribute);
        }

        return !empty($attributes) ? $attributes : null;
    }

    /**
     * Get the product's category tree in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return array|null
     */
    protected function getProductCategories(ProductInterface $product)
    {
        $this->_defaultAttributes[]         = 'category_ids';

        $categoriesTreeItems                = [];
        foreach ($product->getCategoryIds() ?? [] as $categoryId) {
            $categoryTreeItems              = $this->getCategoryTreeItems($categoryId);

            if (!is_null($categoryTreeItems) && count($categoryTreeItems) > 0) {
                $categoriesTreeItems[]      = $categoryTreeItems;
            }
        }

        $categoriesTree                     = $this->getCategoryTree($categoriesTreeItems);

        return !is_null($categoriesTree) && !empty($categoriesTree) ? $categoriesTree : null;
    }

    /**
     * Get the product's options for a certain product in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return array|null
     */
    protected function getProductOptions(ProductInterface $product)
    {
        $isConfigurable = $product->getTypeId() === Configurable::TYPE_CODE;

        $productOptions = [];

        if ($isConfigurable) {
            $children = $product->getTypeInstance()->getUsedProducts($product);

            foreach ($children as $child) {
                if ($child instanceof ProductInterface) {
                    // $child does not contain custom properties
                    $fullChild = $this->getProductById(intval($child->getId()));
                    if (!is_null($fullChild) && $this->checkProductShouldBeExported($child)) {
                        $productOptions[] = $fullChild;
                    }
                }
            }
        } else {
            $productOptions[] = $product;
        }

        $transformed    = [
            'option' => []
        ];

        foreach ($productOptions as $productOption) {
            $option = $this->transformProductOption($productOption);

            if (!is_null($option)) {
                $transformed['option'][] = $option;
            }
        }

        return !empty($transformed['option']) ? $transformed : null;
    }


    /**
     * Get the catalog products for a certain website.
     *
     * @param int $itemsPerPage
     * @param int $page
     * @param bool $isLast
     * @return ProductInterface[]
     */
    protected function getWebsiteCatalog(int $itemsPerPage = 0, int $page = 0, bool &$isLast = true) : array
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder  = $this->_searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder->addFilter(
            'website_id',
            intval($this->_connection->getWebsiteId()),
            'in'
        );

        if ($itemsPerPage !== 0 && $page !== 0) {
            $searchCriteriaBuilder
                ->setPageSize($itemsPerPage)
                ->setCurrentPage($page);
        }

        $searchCriteria         = $searchCriteriaBuilder->create();

        $products               = $this->_productRepository->getList($searchCriteria);
        $productItems           = $products->getItems();

        if ($itemsPerPage !== 0 && $page !== 0) {
            $isLast             = $products->getTotalCount() <= $itemsPerPage * $page;
        } else {
            $isLast             = true;
        }

        return $productItems;
    }


    /**
     * Get the catalog product for a certain website by id.
     *
     * @param int $id
     * @return ProductInterface|null
     */
    protected function getProductById(int $id)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder  = $this->_searchCriteriaBuilderFactory->create();
        $searchCriteria         = $searchCriteriaBuilder
            ->addFilter(
                'website_id',
                intval($this->_connection->getWebsiteId()),
                'in'
            )->addFilter(
                'entity_id',
                $id
            )->create();

        $products               = $this->_productRepository->getList($searchCriteria);
        $productItems           = array_values($products->getItems());

        return isset($productItems[0]) ? $productItems[0] : null;
    }


    /**
     * Add the attribute data with translated titles and values to the attributes output in the EffectConnect Marketplaces SDK expected format.
     *
     * @param array $attributesOutput
     * @param ProductInterface $product
     * @param string $code
     * @param $attribute
     * @return void
     */
    protected function getProductAttributeData(array &$attributesOutput, ProductInterface $product, string $code, $attribute)
    {
        if (in_array($code, $this->_defaultAttributes)) {
            return;
        }

        $value                          = $this->getRawAttributeValue($product, $code);

        if (is_null($value) || empty($value)) {
            return;
        }

        $titleArray                     = $this->getTranslatedAttributeTitles($code, $attribute);

        if (is_array($value)) {
            $isAssociativeArray         = array_keys($value) !== range(0, count($value) - 1);

            if ($isAssociativeArray) {
                $valuesArray            = $this->getAssociativeAttributeValuesArray($value);

                foreach ($valuesArray as $valueArray) {
                    $attributesOutput[] = $valueArray;
                }
            } else {
                $valuesArray            = $this->getSequentialAttributeValueArray($value);

                foreach ($valuesArray as $valueArray) {
                    $attributesOutput[] = $valueArray;
                }
            }
        } else {
            $valueArray                 = $this->getTranslatedAttributeValue($value, $attribute);

            $attributesOutput[]         = [
                'code'                  => [
                    '_cdata'            => $code
                ],
                'names'                 => [
                    'name'              => $titleArray
                ],
                'values'                => [
                    'value'             => $valueArray
                ]
            ];
        }
    }

    /**
     * Get the attribute title for each store-view using the store-view mapping.
     *
     * @param string $code
     * @param Attribute $attribute
     * @return array
     */
    protected function getTranslatedAttributeTitles(string $code, $attribute) : array
    {
        $titles = [];

        foreach ($this->_storeViewMapping as $language => $storeViewId) {
            $titles[]           = [
                '_cdata'        => $this->getTranslatedAttributeTitle($code, $storeViewId, $attribute),
                '_attributes'   => [
                    'language'  => strval($language)
                ]
            ];
        }

        return $titles;
    }

    /**
     * Get the attribute title for a certain store-view.
     *
     * @param string $code
     * @param int $storeViewId
     * @param Attribute $attribute
     * @return string
     */
    protected function getTranslatedAttributeTitle(string $code, int $storeViewId, $attribute) : string
    {
        return $attribute->getStoreLabel($storeViewId) ?: ucwords((new UnderscoreToSeparator)->filter($code));
    }

    /**
     * Get the raw attribute value from a product using the attribute code.
     *
     * @param ProductInterface $product
     * @param string $code
     * @return mixed
     */
    protected function getRawAttributeValue(ProductInterface $product, string $code)
    {
        return $product->getData($code);
    }

    /**
     * Generate a snake-case code (based on the base value - not translated) for the attribute raw value.
     * When multiple values this function returns an array.
     *
     * @param $rawValue
     * @param Attribute $attribute
     * @return string|array
     */
    protected function getAttributeValueCode($rawValue, $attribute)
    {
        $value = $this->getAttributeValueTranslation($rawValue, 0, $attribute);

        if (is_array($value)) {
            return array_map(function ($value) {
                return strtolower((new SeparatorToCamelCase())->filter($value));
            }, $value);
        }

        return strtolower((new SeparatorToCamelCase())->filter($value));
    }

    /**
     * Get a attribute value array with the attribute value code and the translations for the attribute value.
     *
     * @param mixed $rawValue
     * @param Attribute $attribute
     * @return array
     */
    protected function getTranslatedAttributeValue($rawValue, $attribute) : array
    {
        $valueCode                  = $this->getAttributeValueCode($rawValue, $attribute);

        if (is_array($valueCode)) {
            $codeArray              = $valueCode;
            $translatedArray        = $this->getAttributeValueTranslationsArray($rawValue, $attribute);
            $values                 = [];

            for ($i = 0; $i < count($valueCode); $i++) {
                $values[]           = [
                    'code'          => [
                        '_cdata'    => array_values($codeArray)[$i]
                    ],
                    'names'         => [
                        'name'      => array_values($translatedArray)[$i] ?? array_values($codeArray)[$i],
                    ]
                ];
            }

            return $values;
        }

        return [
            'code'                  => [
                '_cdata'            => $valueCode
            ],
            'names'                 => [
                'name'              => $this->getAttributeValueTranslations($rawValue, $attribute)
            ]
        ];
    }

    /**
     * Get the attribute values array from a raw value of the type array (associative).
     *
     * @param mixed $arrayValue
     * @return array
     */
    protected function getAssociativeAttributeValuesArray($arrayValue) : array
    {
        $values                             = [];

        foreach ($arrayValue as $key => $value) {
            if (!is_null($value) && !is_object($value) && !is_array($value)) {
                $stringValue                = is_bool($value) ? ($value ? 'true' : 'false') : strval($value);

                if (is_null($stringValue) || empty($stringValue)) {
                    continue;
                }

                $valueArray                 =  [
                    'code'                  => [
                        '_cdata'            => strtolower((new SeparatorToCamelCase())->filter($key)),
                    ],
                    'names'                 => [
                        'name'              => []
                    ],
                    'values'                => [
                        'value'             => [
                            'code'          => [
                                '_cdata'    => strtolower((new SeparatorToCamelCase())->filter($stringValue)),
                            ],
                            'names'         => [
                                'name'      => []
                            ]
                        ]
                    ]
                ];

                foreach ($this->_storeViewMapping as $language => $storeViewId) {
                    $valueArray['names']['name'][]                     = [
                        '_cdata'            => ucwords((new UnderscoreToSeparator())->filter(strval($key))),
                        '_attributes'       => [
                            'language'      => strval($language)
                        ]
                    ];

                    $valueArray['values']['value']['names']['name'][]  = [
                        '_cdata'            => $stringValue,
                        '_attributes'       => [
                            'language'      => strval($language)
                        ]
                    ];
                }

                $values[]                   = $valueArray;
            }
        }

        return $values;
    }

    /**
     * Get the attribute value array from a raw value of the type array (sequential).
     *
     * @param mixed $arrayValue
     * @return array
     */
    protected function getSequentialAttributeValueArray($arrayValue) : array
    {
        $values                         = [];

        foreach ($arrayValue as $itemValue) {
            if (!is_null($itemValue) && !is_object($itemValue) && !is_array($itemValue)) {
                if (empty($itemValue)) {
                    continue;
                }

                $valueArray             =  [
                    'code'              => [
                        '_cdata'        => strtolower((new SeparatorToCamelCase())->filter($itemValue))
                    ],
                    'names'             => [
                        'name'          => []
                    ]
                ];

                foreach ($this->_storeViewMapping as $language => $storeViewId) {
                    $valueArray['names']['name'][]  = [
                        '_cdata'        => strval($itemValue),
                        '_attributes'   => [
                            'language'  => strval($language)
                        ]
                    ];
                }

                $values[]               = $valueArray;
            }
        }

        return $values;
    }

    /**
     * Get the textual attribute value translations for each store-view using the store-view mapping.
     *
     * @param mixed $rawValue
     * @param Attribute $attribute
     * @return array
     */
    protected function getAttributeValueTranslations($rawValue, $attribute) : array
    {
        $translations           = [];

        foreach ($this->_storeViewMapping as $language => $storeViewId) {
            $valueTitle         = $this->getAttributeValueTranslation($rawValue, $storeViewId, $attribute, true);

            $translations[]     = [
                '_cdata'        => is_bool($valueTitle) ? ($valueTitle ? 'true' : 'false') : strval($valueTitle),
                '_attributes'   => [
                    'language'  => strval($language)
                ]
            ];
        }

        return $translations;
    }

    /**
     * Get the textual attribute value translations (when the translated value is an array) for each store-view using the store-view mapping.
     *
     * @param mixed $rawValue
     * @param Attribute $attribute
     * @return array
     */
    protected function getAttributeValueTranslationsArray($rawValue, $attribute) : array
    {
        $translations               = [];

        foreach ($this->_storeViewMapping as $language => $storeViewId) {
            $valueTitleArray        = array_values($this->getAttributeValueTranslation($rawValue, $storeViewId, $attribute, false));

            if (!is_array($valueTitleArray)) {
                $valueTitleArray    = [$valueTitleArray];
            }

            foreach ($valueTitleArray as $index => $valueTitle) {
                if (!isset($translations[$index])) {
                    $translations[$index]       = [];
                }

                $translations[$index][]         = [
                    '_cdata'        => is_bool($valueTitle) ? ($valueTitle ? 'true' : 'false') : strval($valueTitle),
                    '_attributes'   => [
                        'language'  => strval($language)
                    ]
                ];
            }
        }

        return array_values($translations);
    }

    /**
     * Get the textual attribute value translation for a certain store-view.
     * This can be an array when field is multi-select (unless $forceString is set to true).
     * When $forceString is set to true, array values will be imploded to a comma separated string.
     *
     * @param mixed $rawValue
     * @param int $storeViewId
     * @param Attribute $attribute
     * @param boolean $forceString
     * @return string|array|null
     */
    protected function getAttributeValueTranslation($rawValue, int $storeViewId, $attribute, bool $forceString = false)
    {
        try {
            $attributeSource        = $attribute->getSource();
        } catch (LocalizedException $e) {
            $attributeSource        = strval($rawValue);
        }

        $attributeValueData         = $attributeSource ? $rawValue : null;

        $attributeValueIsOptionable =
            $attributeSource &&
            !is_null($attributeValueData) &&
            (is_string($attributeValueData) ||
                is_numeric($attributeValueData));

        $attribute->setStoreId($storeViewId);

        $attributeValueText         = $attributeValueIsOptionable ? $attributeSource->getOptionText($attributeValueData) : null;

        if ($attributeValueText instanceof Phrase) {
            $attributeValueText     = $attributeValueText->getText();
        }

        $attributeValue             = $attributeValueText ?: $attributeValueData;

        if ($forceString && is_array($attributeValue)) {
            $attributeValue         = implode(',', $attributeValue);
        }

        return $attributeValue ?? null;
    }


    /**
     * Get the category tree items for a certain product.
     *
     * @param int $id
     * @return array|null
     */
    protected function getCategoryTreeItems(int $id)
    {
        $tree           = [];

        try {
            $category   = $this->_categoryRepository->get($id);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        do {
            array_unshift($tree, $this->getCategoryStructure($category));

            if (!is_null($category->getParentId())) {
                try {
                    $category   = $this->_categoryRepository->get($category->getParentId());
                } catch (NoSuchEntityException $e) {
                    $category   = null;
                }
            }
        } while (!is_null($category));

        return $tree;
    }

    /**
     * Get the category tree structure for a certain category in the EffectConnect Marketplaces SDK expected format.
     *
     * @param CategoryInterface $category
     * @return array
     */
    protected function getCategoryStructure(CategoryInterface $category) : array
    {
        $translated =   [
            'title' => []
        ];

        foreach ($this->_storeViewMapping as $language => $storeViewId) {
            try {
                $storeViewCategory  =  $this->_categoryRepository->get($category->getId(), $storeViewId);
            } catch (NoSuchEntityException $e) {
                continue;
            }

            $translated['title'][]  =  [
                '_cdata'            => strval($storeViewCategory->getName()),
                '_attributes'       => [
                    'language'      => strval($language)
                ]
            ];
        }

        return [
            'id'        => intval($category->getId()),
            'titles'    => $translated
        ];
    }

    /**
     * Get the category tree for multiple categories in the EffectConnect Marketplaces SDK expected format.
     *
     * @param array $categoriesTreeItems
     * @return mixed|null
     */
    protected function getCategoryTree(array $categoriesTreeItems)
    {
        $categoryTree           = [];

        foreach ($categoriesTreeItems as $categoryTreeItems) {
            $treeHead           = &$categoryTree;
            $categoryCounter    = 0;

            foreach ($categoryTreeItems as $category) {
                $categoryId     = $category['id'];

                if (isset($treeHead['category'][$categoryId])) {
                    $treeHead['category'][$categoryId]  = $treeHead['category'][$categoryId] + $category;
                } else {
                    $treeHead['category'][$categoryId]  = $category;
                }

                $categoryCounter++;
                if ($categoryCounter < count($categoryTreeItems)) {
                    if (!isset($treeHead['category'][$categoryId]['children'])) {
                        $treeHead['category'][$categoryId]['children']  = [];
                    }
                    $treeHead   = &$treeHead['category'][$categoryId]['children'];
                }
            }
        }

        return $categoryTree;
    }

    /**
     * Get the number of products per page when obtaining the catalog.
     *
     * @return int
     */
    protected function getPageSize() : int
    {
        $settingsPageSize = $this->_settingsHelper->getCatalogExportPageSize(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId()));
        return intval($settingsPageSize ?? static::DEFAULT_PAGE_SIZE);
    }

    /**
     * Get a product's attribute value (textual when available) using it's code in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @param null|string $attributeCode
     * @param string $valueType
     * @param int $max
     * @param bool $unsigned,
     * @param mixed $default,
     * @return mixed
     */
    protected function getProductAttribute(
        ProductInterface $product,
        $attributeCode,
        string $valueType,
        int $max        = 0,
        bool $unsigned  = false,
        $default        = null
    ) {
        if (is_null($attributeCode)) {
            return $default;
        }

        $attributeResource  = $product->getResource()->getAttribute($attributeCode);
        $attributeSource    = $attributeResource ? $attributeResource->getSource() : null;
        $attributeValue     = $attributeSource ? $product->getData($attributeCode) : null;
        $attributeValueText = $attributeSource && !is_null($attributeValue) && (is_string($attributeValue) || is_numeric($attributeValue)) ? $attributeSource->getOptionText($attributeValue) : null;
        $attributeValue     = $attributeValueText ?: $attributeValue;

        if ($attributeValue instanceof Phrase) {
            $attributeValue = $attributeValue->getText();
        }

        if (!$attributeValue) {
            return $default;
        }

        if (
            ($valueType !== static::VALUE_TYPE_ARRAY && is_array($attributeValue)) ||
            ($valueType === static::VALUE_TYPE_ARRAY && !is_array($attributeValue))
        ) {
            return $default;
        }

        switch ($valueType) {
            case static::VALUE_TYPE_INT:
                $attributeValue = intval($attributeValue);
                if ($max > 0 && $attributeValue > $max) {
                    $this->writeToLog(LogCode::CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MAXIMUM(), [
                        intval($this->_connection->getEntityId()),
                        intval($product->getId() ?? 0),
                        $attributeCode,
                        $attributeValue
                    ]);
                    $attributeValue = $max;
                }
                if ($unsigned && $attributeValue < 0) {
                    $this->writeToLog(LogCode::CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MINIMUM(), [
                        intval($this->_connection->getEntityId()),
                        intval($product->getId() ?? 0),
                        $attributeCode,
                        $attributeValue
                    ]);
                    $attributeValue = 0;
                }
                break;
            case static::VALUE_TYPE_FLOAT:
                $attributeValue = floatval($attributeValue);
                if ($max > 0 && $attributeValue > $max) {
                    $this->writeToLog(LogCode::CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MAXIMUM(), [
                        intval($this->_connection->getEntityId()),
                        intval($product->getId() ?? 0),
                        $attributeCode,
                        $attributeValue
                    ]);
                    $attributeValue = $max;
                }
                if ($unsigned && $attributeValue < 0) {
                    $this->writeToLog(LogCode::CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MINIMUM(), [
                        intval($this->_connection->getEntityId()),
                        intval($product->getId() ?? 0),
                        $attributeCode,
                        $attributeValue
                    ]);
                    $attributeValue = 0;
                }
                break;
            case static::VALUE_TYPE_BOOL:
                $attributeValue = boolval($attributeValue);
                break;
            case static::VALUE_TYPE_ARRAY:
                break;
            case static::VALUE_TYPE_STRING:
            default:
                $attributeValue = strval($attributeValue);
                if ($max > 0 && strlen($attributeValue) > $max) {
                    $this->writeToLog(LogCode::CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MAXIMUM(), [
                        intval($this->_connection->getEntityId()),
                        intval($product->getId() ?? 0),
                        $attributeCode,
                        $attributeValue
                    ]);
                    $attributeValue = substr($attributeValue, 0, $max);
                }
                break;
        }

        return $attributeValue;
    }

    /**
     * Add a value to an array.
     *
     * @param array $array
     * @param $key
     * @param $value
     * @param bool $obligated
     * @param $default
     * @param bool $asCdataWhenString
     * @return void
     * @throws CatalogExportObligatedAttributeIsNullException
     */
    protected function setValueToArray(
        array &$array,
        string $key,
        $value,
        bool $obligated         = false,
        $default                = null,
        bool $asCdataWhenString = true
    ) {
        if (is_null($value) && !$obligated) {
            return;
        }

        $outputValue = $array[$key] = is_null($value) ? $default : $value;

        if (is_null($outputValue) && $obligated) {
            throw new CatalogExportObligatedAttributeIsNullException(__('Missing obligated attribute (%1) in product.', $key));
        }

        if (is_string($outputValue) && $asCdataWhenString) {
            $array[$key] =  [
                '_cdata' => $outputValue
            ];
        } else {
            $array[$key] = $outputValue;
        }
    }

    /**
     * Write to the catalog export log.
     *
     * @param LogCode $logCode
     * @param array $parameters
     * @return bool
     */
    protected function writeToLog(LogCode $logCode, array $parameters = [])
    {
        switch ($logCode) {
            case LogCode::CATALOG_EXPORT_HAS_STARTED():
                return $this->_logHelper->logCatalogExportStarted(...$parameters);
            case LogCode::CATALOG_EXPORT_HAS_ENDED():
                return $this->_logHelper->logCatalogExportEnded(...$parameters);
            case LogCode::CATALOG_EXPORT_CONNECTION_FAILED():
                return $this->_logHelper->logCatalogExportConnectionFailed(...$parameters);
            case LogCode::CATALOG_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET():
                return $this->_logHelper->logCatalogExportObligatedAttributeNotSet(...$parameters);
            case LogCode::CATALOG_EXPORT_EAN_NOT_VALID():
                return $this->_logHelper->logCatalogExportEanNotValid(...$parameters);
            case LogCode::CATALOG_EXPORT_EAN_ALREADY_IN_USE():
                return $this->_logHelper->logCatalogExportEanAlreadyInUse(...$parameters);
            case LogCode::CATALOG_EXPORT_PRODUCT_HAS_NO_VALID_OPTIONS():
                return $this->_logHelper->logCatalogExportProductHasNoValidOptions(...$parameters);
            case LogCode::CATALOG_EXPORT_PRODUCT_NOT_FOUND():
                return $this->_logHelper->logCatalogExportProductNotFound(...$parameters);
            case LogCode::CATALOG_EXPORT_FILE_CREATION_FAILED():
                return $this->_logHelper->logCatalogExportXmlFileCreationFailed(...$parameters);
            case LogCode::CATALOG_EXPORT_XML_GENERATION_FAILED():
                return $this->_logHelper->logCatalogExportXmlGenerationFailed(...$parameters);
            case LogCode::CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MAXIMUM():
                return $this->_logHelper->logCatalogExportAttributeValueReachedMaximum(...$parameters);
            case LogCode::CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MINIMUM():
                return $this->_logHelper->logCatalogExportAttributeValueReachedMinimum(...$parameters);
            case LogCode::CATALOG_EXPORT_PRODUCT_NOT_ENABLED():
                return $this->_logHelper->logCatalogExportProductNotEnabled(...$parameters);
            case LogCode::CATALOG_EXPORT_PRODUCT_NOT_VISIBLE():
                return $this->_logHelper->logCatalogExportProductNotVisible(...$parameters);
            case LogCode::CATALOG_EXPORT_PRODUCT_TYPE_NOT_SUPPORTED():
                return $this->_logHelper->logCatalogExportProductTypeNotSupported(...$parameters);
            case LogCode::CATALOG_EXPORT_MAXIMUM_IMAGES_EXCEEDED():
                return $this->_logHelper->logCatalogExportMaximumImagesExceeded(...$parameters);
            case LogCode::CATALOG_EXPORT_NO_STOREVIEW_MAPPING_DEFINED():
                return $this->_logHelper->logCatalogExportNoStoreviewMappingDefined(...$parameters);
            case LogCode::CATALOG_EXPORT_PRODUCT_HAS_NO_SKU():
                return $this->_logHelper->logCatalogExportProductHasNoSku(...$parameters);
            default:
                return false;
        }
    }

    /**
     * For the catalog export we will do a duplicate EAN check in case the EAN attribute should be exported.
     * @return bool
     */
    protected function checkForDuplicateEans()
    {
        return boolval($this->_settingsHelper->getCatalogExportUseAndValidateEan(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId())));
    }
}
