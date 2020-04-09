<?php

namespace EffectConnect\Marketplaces\Traits\Log;

use EffectConnect\Marketplaces\Enums\LogCode;
use EffectConnect\Marketplaces\Enums\LogSubjectType;
use EffectConnect\Marketplaces\Enums\LogType;
use EffectConnect\Marketplaces\Enums\Process;
use EffectConnect\Marketplaces\Objects\Loggable;

/**
 * Trait CatalogLoggablesTrait
 * @package EffectConnect\Marketplaces\Traits\Log
 */
trait CatalogLoggablesTrait
{
    /**
     * Log when the catalog export has started.
     *
     * @param int $connectionId
     * @return bool
     */
    public function logCatalogExportStarted(int $connectionId) : bool
    {
        $loggable = new Loggable(
            LogType::INFO(),
            LogCode::CATALOG_EXPORT_HAS_STARTED(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Exporting the connection %s catalog to EffectConnect Marketplaces has started.',
            [
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::CONNECTION(),
            $connectionId
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when the catalog export has ended.
     *
     * @param int $connectionId
     * @param bool $succeeded
     * @param array $errorData
     * @return bool
     */
    public function logCatalogExportEnded(int $connectionId, bool $succeeded, array $errorData = []) : bool
    {
        $loggable = new Loggable(
            $succeeded ? LogType::SUCCESS() : LogType::FATAL(),
            LogCode::CATALOG_EXPORT_HAS_ENDED(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Exporting the connection %s catalog to EffectConnect Marketplaces has ended.',
            [
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::CONNECTION(),
            $connectionId
        );

        $loggable->setPayload(json_encode([
            'error_data' => $errorData
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when the catalog export has failed for a certain connection.
     *
     * @param int $connectionId
     * @param array $errorData
     * @return bool
     */
    public function logCatalogExportConnectionFailed(int $connectionId, array $errorData = []) : bool
    {
        $loggable = new Loggable(
            LogType::FATAL(),
            LogCode::CATALOG_EXPORT_CONNECTION_FAILED(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Exporting the connection %s catalog to EffectConnect Marketplaces has failed.',
            [
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::CONNECTION(),
            $connectionId
        );

        $loggable->setPayload(json_encode([
            'error_data' => $errorData
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when an obligated attribute is not set.
     *
     * @param int $connectionId
     * @param int $productId
     * @param string $attributeKey
     * @return bool
     */
    public function logCatalogExportObligatedAttributeNotSet(int $connectionId, int $productId, string $attributeKey) : bool
    {
        $loggable = new Loggable(
            LogType::WARNING(),
            LogCode::CATALOG_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Product %s can not be included in the catalog export because the obligated %s attribute does not contain a value (connection %s).',
            [
                $productId,
                $attributeKey,
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::PRODUCT(),
            $productId
        );

        $loggable->setPayload(json_encode([
            'attribute' => $attributeKey
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when a product is not enabled.
     *
     * @param int $connectionId
     * @param int $productId
     * @return bool
     */
    public function logCatalogExportProductNotEnabled(int $connectionId, int $productId) : bool
    {
        $loggable = new Loggable(
            LogType::NOTICE(),
            LogCode::CATALOG_EXPORT_PRODUCT_NOT_ENABLED(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Product %s is not included in the catalog export because the product is not enabled (connection %s).',
            [
                $productId,
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::PRODUCT(),
            $productId
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when a product is not visible.
     *
     * @param int $connectionId
     * @param int $productId
     * @return bool
     */
    public function logCatalogExportProductNotVisible(int $connectionId, int $productId) : bool
    {
        $loggable = new Loggable(
            LogType::NOTICE(),
            LogCode::CATALOG_EXPORT_PRODUCT_NOT_VISIBLE(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Product %s is not included in the catalog export because the product is not visible (connection %s).',
            [
                $productId,
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::PRODUCT(),
            $productId
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when a product type is not supported.
     *
     * @param int $connectionId
     * @param int $productId
     * @param string $productType
     * @return bool
     */
    public function logCatalogExportProductTypeNotSupported(int $connectionId, int $productId, string $productType) : bool
    {
        $loggable = new Loggable(
            LogType::NOTICE(),
            LogCode::CATALOG_EXPORT_PRODUCT_TYPE_NOT_SUPPORTED(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Product %s can not be included in the catalog export because the product type (%s) is not supported (connection %s).',
            [
                $productId,
                $productType,
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::PRODUCT(),
            $productId
        );

        $loggable->setPayload(json_encode([
            'product_type' => $productType
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when an EAN is not valid.
     *
     * @param int $connectionId
     * @param int $productId
     * @param string $ean
     * @return bool
     */
    public function logCatalogExportEanNotValid(int $connectionId, int $productId, string $ean) : bool
    {
        $loggable = new Loggable(
            LogType::WARNING(),
            LogCode::CATALOG_EXPORT_EAN_NOT_VALID(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Product %s can not be included in the catalog export because the EAN attribute does not contain a valid EAN-13 value (connection %s).',
            [
                $productId,
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::PRODUCT(),
            $productId
        );

        $loggable->setPayload(json_encode([
            'ean' => $ean
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when an EAN is set in the catalog multiple times.
     *
     * @param int $connectionId
     * @param int $productId
     * @param string $ean
     * @return bool
     */
    public function logCatalogExportEanAlreadyInUse(int $connectionId, int $productId, string $ean) : bool
    {
        $loggable = new Loggable(
            LogType::WARNING(),
            LogCode::CATALOG_EXPORT_EAN_ALREADY_IN_USE(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Product %s can not be included in the catalog export because the EAN `%s` is already in use by another product (connection %s).',
            [
                $productId,
                $ean,
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::PRODUCT(),
            $productId
        );

        $loggable->setPayload(json_encode([
            'ean' => $ean
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when a product has no (valid) product options.
     *
     * @param int $connectionId
     * @param int $productId
     * @return bool
     */
    public function logCatalogExportProductHasNoValidOptions(int $connectionId, int $productId) : bool
    {
        $loggable = new Loggable(
            LogType::WARNING(),
            LogCode::CATALOG_EXPORT_PRODUCT_HAS_NO_VALID_OPTIONS(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Product %s can not be included in the catalog export because it has no (valid) product options (connection %s).',
            [
                $productId,
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::PRODUCT(),
            $productId
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when a product is not found by it's ID.
     *
     * @param int $connectionId
     * @param int $productId
     * @return bool
     */
    public function logCatalogExportProductNotFound(int $connectionId, int $productId) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::CATALOG_EXPORT_PRODUCT_NOT_FOUND(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Product %s can not be included in the catalog export because it not found (connection %s).',
            [
                $productId,
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::PRODUCT(),
            $productId
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when creating the XML file failed.
     *
     * @param int $connectionId
     * @param string $fileLocation
     * @return bool
     */
    public function logCatalogExportXmlFileCreationFailed(int $connectionId, string $fileLocation) : bool
    {
        $loggable = new Loggable(
            LogType::FATAL(),
            LogCode::CATALOG_EXPORT_FILE_CREATION_FAILED(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'An error occurred while creating the export XML file `%s` (connection %s).',
            [
                $fileLocation,
                $connectionId
            ]
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when generating XML failed.
     *
     * @param int $connectionId
     * @param int $productId
     * @return bool
     */
    public function logCatalogExportXmlGenerationFailed(int $connectionId, int $productId = 0) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::CATALOG_EXPORT_XML_GENERATION_FAILED(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $messageData = [
            $connectionId
        ];

        if ($productId !== 0) {
            array_unshift($messageData, $productId);
        }

        $loggable->setFormattedMessage(
            'An error occurred while generating XML' . ($productId !== 0 ? ' for product %s' : '') . ' (connection %s).',
            $messageData
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when generating XML failed.
     *
     * @param int $connectionId
     * @param int $productId
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function logCatalogExportAttributeValueReachedMaximum(int $connectionId, int $productId, string $attribute, $value) : bool
    {
        $loggable = new Loggable(
            LogType::NOTICE(),
            LogCode::CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MAXIMUM(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'The value for attribute `%s` (product %s) reached the maximum (length or value), and will be capped at the maximum (connection %s).',
            [
                $attribute,
                $productId,
                $connectionId
            ]
        );

        $loggable->setPayload(json_encode([
            'value' => strval($value)
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when generating XML failed.
     *
     * @param int $connectionId
     * @param int $productId
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function logCatalogExportAttributeValueReachedMinimum(int $connectionId, int $productId, string $attribute, $value) : bool
    {
        $loggable = new Loggable(
            LogType::NOTICE(),
            LogCode::CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MINIMUM(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'The value for attribute `%s` (product %s) reached the minimum (length or value) (connection %s).',
            [
                $attribute,
                $productId,
                $connectionId
            ]
        );

        $loggable->setPayload(json_encode([
            'value' => strval($value)
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when a product exceeds maximum amount of images.
     *
     * @param int $connectionId
     * @param int $productId
     * @param int $maxImagesAmount
     * @param int $numberOfImages
     * @return bool
     */
    public function logCatalogExportMaximumImagesExceeded(int $connectionId, int $productId, int $maxImagesAmount, int $numberOfImages) : bool
    {
        $loggable = new Loggable(
            LogType::NOTICE(),
            LogCode::CATALOG_EXPORT_MAXIMUM_IMAGES_EXCEEDED(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Not all images for product %s can be included in the catalog export because the maximum amount of images for a product is exceeded, this product has %s images, only the first %s will be included in the export (connection %s).',
            [
                $productId,
                $numberOfImages,
                $maxImagesAmount,
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::PRODUCT(),
            $productId
        );

        $loggable->setPayload(json_encode([
            'images_amount' => $numberOfImages,
            'images_maximum' => $maxImagesAmount
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when a connection has no mapped storeviews.
     *
     * @param int $connectionId
     * @return bool
     */
    public function logCatalogExportNoStoreviewMappingDefined(int $connectionId) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::CATALOG_EXPORT_NO_STOREVIEW_MAPPING_DEFINED(),
            Process::EXPORT_CATALOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'No storeview mapping for connection %s.',
            [
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::CONNECTION(),
            $connectionId
        );

        return $this->insertLogItem($loggable);
    }
}