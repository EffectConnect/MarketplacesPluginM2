<?php

namespace EffectConnect\Marketplaces\Helper\Transformer;

use EffectConnect\Marketplaces\Enums\LogCode;
use EffectConnect\Marketplaces\Exception\CatalogExportObligatedAttributeIsNullException;
use EffectConnect\Marketplaces\Helper\SettingsHelper;
use EffectConnect\Marketplaces\Model\Connection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * The OffersExportTransformer obtains the offers from a certain website in the Magento 2 installation.
 * Then it transforms those offers into a format that can be used with the EffectConnect Marketplaces SDK.
 * Offers contain product data that can change more often like price, stock and delivery-time.
 * The OffersExportTransformer class extends the CatalogExportTransformer class because it's basically a stripped version of the CatalogTransformer.
 *
 * Class OffersExportTransformer
 * @package EffectConnect\Marketplaces\Helper\Transformer
 */
class OffersExportTransformer extends CatalogExportTransformer
{
    /**
     * Get the offers, transformed to the EffectConnect Marketplaces SDK expected format as an array.
     *
     * @param Connection $connection
     * @return array
     */
    public function get(Connection $connection) : array
    {
        $this->_connection  = $connection;

        $this->setStoreViewMapping();

        return $this->transformOffers(
            $this->getWebsiteCatalog()
        );
    }

    /**
     * Get the offers, transformed to the EffectConnect Marketplaces SDK expected format as an XML string.
     *
     * @param Connection $connection
     * @return string
     */
    public function getXml(Connection $connection) : string
    {
        return parent::getXml($connection);
    }

    /**
     * Save offers as XML to an XML file.
     *
     * @param Connection $connection
     * @param string $exportFileName
     * @return bool|string
     */
    public function saveXml(Connection $connection, string $exportFileName = 'offers')
    {
        return parent::saveXml($connection, $exportFileName);
    }


    /**
     * Transform the catalog products to offers in the EffectConnect Marketplaces SDK expected format.
     *
     * @param array $products
     * @return array
     */
    protected function transformOffers(array $products) : array
    {
        return parent::transformCatalog($products, false);
    }

    /**
     * Transform and write offers to EffectConnect Marketplaces desired XML format (segmented per product).
     *
     * @param Connection $connection
     * @param string $exportFileName
     * @param bool $checkForDuplicateEans
     * @param ProductInterface[] $catalog
     * @return bool|string
     */
    public function saveXmlSegmented(Connection $connection, string $exportFileName = 'offers', bool $checkForDuplicateEans = false, array $catalog = null)
    {
        return parent::saveXmlSegmented($connection, $exportFileName, $checkForDuplicateEans, $catalog);
    }

    /**
     * Transform a certain product to a offer in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductInterface $product
     * @return array|null
     */
    protected function transformProduct(ProductInterface $product)
    {
        try {
            $product = $this->_productRepository->getById($product->getId(), false, 0);
        } catch (NoSuchEntityException $e) {
            $this->writeToLog(LogCode::OFFERS_EXPORT_PRODUCT_NOT_FOUND(), [
                intval($this->_connection->getEntityId()),
                intval($product->getId())
            ]);
            return null;
        }

        if (!($product instanceof ProductInterface)) {
            $this->writeToLog(LogCode::OFFERS_EXPORT_PRODUCT_NOT_FOUND(), [
                intval($this->_connection->getEntityId()),
                intval($product->getId())
            ]);
            return null;
        }

        $identifier     = $this->getProductIdentifier($product);
        $options        = $this->getProductOptions($product);

        $transformed    = [];

        try {
            $this->setValueToArray($transformed, 'identifier', $identifier, true);
            $this->setValueToArray($transformed, 'options', $options, true, []);
        } catch (CatalogExportObligatedAttributeIsNullException $e) {
            $this->writeToLog(LogCode::OFFERS_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET(), [
                intval($this->_connection->getEntityId()),
                intval($identifier),
                strval($e->getParameters()[0] ?? '')
            ]);
            return null;
        }

        if (empty($transformed['options'])) {
            return null;
        }

        return $transformed;
    }

    /**
     * Transform a certain product option to a offer in the EffectConnect Marketplaces SDK expected format.
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
        $deliveryTime   = $this->getProductDeliveryTime($productOption);

        $transformed    = [];

        try {
            $this->setValueToArray($transformed, 'identifier', $identifier, true);
            $this->setValueToArray($transformed, 'cost', $cost);
            $this->setValueToArray($transformed, 'price', $price);
            $this->setValueToArray($transformed, 'priceOriginal', $priceOriginal);
            $this->setValueToArray($transformed, 'stock', $stock);
            $this->setValueToArray($transformed, 'deliveryTime', $deliveryTime);
        } catch (CatalogExportObligatedAttributeIsNullException $e) {
            $this->writeToLog(LogCode::OFFERS_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET(), [
                intval($this->_connection->getEntityId()),
                intval($identifier),
                strval($e->getParameters()[0] ?? '')
            ]);
            return null;
        }

        return $transformed;
    }

    /**
     * Get the number of products per page when obtaining the catalog.
     *
     * @return int
     */
    protected function getPageSize() : int
    {
        $settingsPageSize = $this->_settingsHelper->getOfferExportPageSize(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId()));
        return intval($settingsPageSize ?? static::DEFAULT_PAGE_SIZE);
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
            case LogCode::OFFERS_EXPORT_HAS_STARTED():
                return $this->_logHelper->logOffersExportStarted(...$parameters);
            case LogCode::CATALOG_EXPORT_HAS_ENDED():
            case LogCode::OFFERS_EXPORT_HAS_ENDED():
                return $this->_logHelper->logOffersExportEnded(...$parameters);
            case LogCode::CATALOG_EXPORT_CONNECTION_FAILED():
            case LogCode::OFFERS_EXPORT_CONNECTION_FAILED():
                return $this->_logHelper->logOffersExportConnectionFailed(...$parameters);
            case LogCode::CATALOG_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET():
            case LogCode::OFFERS_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET():
                return $this->_logHelper->logOffersExportObligatedAttributeNotSet(...$parameters);
            case LogCode::CATALOG_EXPORT_PRODUCT_NOT_FOUND():
            case LogCode::OFFERS_EXPORT_PRODUCT_NOT_FOUND():
                return $this->_logHelper->logOffersExportProductNotFound(...$parameters);
            case LogCode::CATALOG_EXPORT_FILE_CREATION_FAILED():
            case LogCode::OFFERS_EXPORT_FILE_CREATION_FAILED():
                return $this->_logHelper->logOffersExportXmlFileCreationFailed(...$parameters);
            case LogCode::CATALOG_EXPORT_XML_GENERATION_FAILED():
            case LogCode::OFFERS_EXPORT_XML_GENERATION_FAILED():
                return $this->_logHelper->logOffersExportXmlGenerationFailed(...$parameters);
            case LogCode::CATALOG_EXPORT_EAN_NOT_VALID():
            case LogCode::CATALOG_EXPORT_EAN_ALREADY_IN_USE():
            case LogCode::CATALOG_EXPORT_PRODUCT_HAS_NO_VALID_OPTIONS():
            case LogCode::CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MAXIMUM():
            case LogCode::CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MINIMUM():
            case LogCode::CATALOG_EXPORT_PRODUCT_NOT_ENABLED():
            case LogCode::CATALOG_EXPORT_PRODUCT_NOT_VISIBLE():
            case LogCode::CATALOG_EXPORT_PRODUCT_TYPE_NOT_SUPPORTED():
            default:
                return false;
        }
    }
}
