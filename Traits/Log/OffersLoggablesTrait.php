<?php

namespace EffectConnect\Marketplaces\Traits\Log;

use EffectConnect\Marketplaces\Enums\LogCode;
use EffectConnect\Marketplaces\Enums\LogSubjectType;
use EffectConnect\Marketplaces\Enums\LogType;
use EffectConnect\Marketplaces\Enums\Process;
use EffectConnect\Marketplaces\Objects\Loggable;

/**
 * Trait OffersLoggablesTrait
 * @package EffectConnect\Marketplaces\Traits\Log
 */
trait OffersLoggablesTrait
{
    /**
     * Log when the offers export has started.
     *
     * @param int $connectionId
     * @return bool
     */
    public function logOffersExportStarted(int $connectionId) : bool
    {
        $loggable = new Loggable(
            LogType::INFO(),
            LogCode::OFFERS_EXPORT_HAS_STARTED(),
            Process::EXPORT_OFFERS(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Exporting the connection %s offers to EffectConnect Marketplaces has started.',
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
     * Log when the offers export has ended.
     *
     * @param int $connectionId
     * @param bool $succeeded
     * @param array $errorData
     * @return bool
     */
    public function logOffersExportEnded(int $connectionId, bool $succeeded, array $errorData = []) : bool
    {
        $loggable = new Loggable(
            $succeeded ? LogType::SUCCESS() : LogType::FATAL(),
            LogCode::OFFERS_EXPORT_HAS_ENDED(),
            Process::EXPORT_OFFERS(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Exporting the connection %s offers to EffectConnect Marketplaces has ended.',
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
     * Log when the offer export for a product to a connection was successful.
     *
     * @param int $connectionId
     * @param int $productId
     * @return bool
     */
    public function logOffersExportProductSuccess(int $connectionId, int $productId) : bool
    {
        $loggable = new Loggable(
            LogType::SUCCESS(),
            LogCode::OFFERS_EXPORT_PRODUCT_SUCCESS(),
            Process::EXPORT_OFFERS(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'The offer for product %s for connection %s was successfully exported to EffectConnect Marketplaces.',
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
     * Log when the offer export for a product to a connection has failed.
     *
     * @param int $connectionId
     * @param int $productId
     * @param array $errorData
     * @return bool
     */
    public function logOffersExportProductFailed(int $connectionId, int $productId, array $errorData = []) : bool
    {
        $loggable = new Loggable(
            LogType::FATAL(),
            LogCode::OFFERS_EXPORT_PRODUCT_FAILED(),
            Process::EXPORT_OFFERS(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'The offer for product %s for connection %s failed to export to EffectConnect Marketplaces.',
            [
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::PRODUCT(),
            $productId
        );

        $loggable->setPayload(json_encode([
            'error_data' => $errorData
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when the offers export has failed for a certain connection.
     *
     * @param int $connectionId
     * @param array $errorData
     * @return bool
     */
    public function logOffersExportConnectionFailed(int $connectionId, array $errorData = []) : bool
    {
        $loggable = new Loggable(
            LogType::FATAL(),
            LogCode::OFFERS_EXPORT_CONNECTION_FAILED(),
            Process::EXPORT_OFFERS(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Exporting the connection %s offers to EffectConnect Marketplaces has failed.',
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
    public function logOffersExportObligatedAttributeNotSet(int $connectionId, int $productId, string $attributeKey) : bool
    {
        $loggable = new Loggable(
            LogType::WARNING(),
            LogCode::OFFERS_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET(),
            Process::EXPORT_OFFERS(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Product %s can not be included in the offers export because the obligated %s attribute does not contain a value (connection %s).',
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
     * Log when a product is not found by it's ID.
     *
     * @param int $connectionId
     * @param int $productId
     * @return bool
     */
    public function logOffersExportProductNotFound(int $connectionId, int $productId) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::OFFERS_EXPORT_PRODUCT_NOT_FOUND(),
            Process::EXPORT_OFFERS(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Product %s can not be included in the offers export because it not found (connection %s).',
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
    public function logOffersExportXmlFileCreationFailed(int $connectionId, string $fileLocation) : bool
    {
        $loggable = new Loggable(
            LogType::FATAL(),
            LogCode::OFFERS_EXPORT_FILE_CREATION_FAILED(),
            Process::EXPORT_OFFERS(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'An error occurred while creating the offers export XML file `%s` (connection %s).',
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
    public function logOffersExportXmlGenerationFailed(int $connectionId, int $productId = 0) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::OFFERS_EXPORT_XML_GENERATION_FAILED(),
            Process::EXPORT_OFFERS(),
            $connectionId
        );

        $messageData = [
            $connectionId
        ];

        if ($productId !== 0) {
            array_unshift($messageData, $productId);
        }

        $loggable->setFormattedMessage(
            'An error occurred while generating offers XML' . ($productId !== 0 ? ' for product %s' : '') . ' (connection %s).',
            $messageData
        );

        return $this->insertLogItem($loggable);
    }
}