<?php

namespace EffectConnect\Marketplaces\Traits\Log;

use EffectConnect\Marketplaces\Enums\LogCode;
use EffectConnect\Marketplaces\Enums\LogType;
use EffectConnect\Marketplaces\Enums\Process;
use EffectConnect\Marketplaces\Objects\Loggable;

/**
 * Trait LogLoggablesTrait
 * @package EffectConnect\Marketplaces\Traits\Log
 */
trait LogLoggablesTrait
{
    /**
     * Log when the log is cleaned.
     *
     * @param int $removedEntries
     * @return bool
     */
    public function logLogCleaned(int $removedEntries) : bool
    {
        $loggable = new Loggable(
            LogType::INFO(),
            LogCode::LOG_LOG_CLEANED(),
            Process::CLEAN_LOG(),
            0
        );

        $loggable->setFormattedMessage(
            'The log has been cleaned according to the expiration configuration (%s entries removed).',
            [
                $removedEntries
            ]
        );

        $loggable->setPayload(json_encode([
            'removed_entries' => $removedEntries
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param string $errorMessage
     * @return bool
     */
    public function logExportConnectionError(int $connectionId, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::LOG_EXPORT_CONNECTION_ERROR(),
            Process::EXPORT_LOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'The log export failed because the connection could not be established (details: %s).',
            [
                $errorMessage
            ]
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param string $errorMessage
     * @return bool
     */
    public function logExportSaveError(int $connectionId, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::LOG_EXPORT_SAVE_ERROR(),
            Process::EXPORT_LOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'The log export failed when saving the queue item (details: %s).',
            [
                $errorMessage
            ]
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param string $errorMessage
     * @return bool
     */
    public function logExportAllowedCallError(int $connectionId, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::LOG_EXPORT_ALLOWED_CALL_ERROR(),
            Process::EXPORT_LOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'The API call to EffectConnect Marketplaces to check if exporting log is allowed failed (details: %s).',
            [
                $errorMessage
            ]
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param string $errorMessage
     * @return bool
     */
    public function logExportXmlGenerationFailed(int $connectionId, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::LOG_EXPORT_XML_GENERATION_FAILED(),
            Process::EXPORT_LOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'The log export failed because the log XML file could not be generated (details: %s).',
            [
                $errorMessage
            ]
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param string $errorMessage
     * @return bool
     */
    public function logExportCreateError(int $connectionId, string $errorMessage) : bool
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::LOG_EXPORT_CREATE_ERROR(),
            Process::EXPORT_LOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'The log export failed when trying to send the log to EffectConnect Marketplaces (details: %s).',
            [
                $errorMessage
            ]
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
    public function logLogExportXmlFileCreationFailed(int $connectionId, string $fileLocation) : bool
    {
        $loggable = new Loggable(
            LogType::FATAL(),
            LogCode::LOG_EXPORT_FILE_CREATION_FAILED(),
            Process::EXPORT_LOG(),
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
     * Log when log export succeeded.
     *
     * @param int $connectionId
     * @return bool
     */
    public function logLogExportSucceeded(int $connectionId) : bool
    {
        $loggable = new Loggable(
            LogType::SUCCESS(),
            LogCode::LOG_EXPORT_SUCCEEDED(),
            Process::EXPORT_LOG(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'The log is successfully exported to EffectConnect Marketplaces (connection %s).',
            [
                $connectionId
            ]
        );

        return $this->insertLogItem($loggable);
    }
}