<?php

namespace EffectConnect\Marketplaces\Helper;

use EffectConnect\Marketplaces\Enums\LogCode;
use EffectConnect\Marketplaces\Enums\LogExpiration;
use EffectConnect\Marketplaces\Enums\LogSubjectType;
use EffectConnect\Marketplaces\Enums\LogType;
use EffectConnect\Marketplaces\Enums\Process;
use EffectConnect\Marketplaces\Model\Log as LogModel;
use EffectConnect\Marketplaces\Model\LogFactory as LogModelFactory;
use EffectConnect\Marketplaces\Model\ResourceModel\Log as LogModelResource;
use EffectConnect\Marketplaces\Model\ResourceModel\Log\Collection as LogModelResourceCollection;
use EffectConnect\Marketplaces\Objects\Loggable;
use EffectConnect\Marketplaces\Traits\Log\CatalogLoggablesTrait;
use EffectConnect\Marketplaces\Traits\Log\OffersLoggablesTrait;
use EffectConnect\Marketplaces\Traits\Log\LogLoggablesTrait;
use EffectConnect\Marketplaces\Traits\Log\OrderLoggablesTrait;
use EffectConnect\Marketplaces\Traits\Log\ShipmentExportLoggablesTrait;
use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;

/**
 * This helper class helps writing entries to the log and obtaining them.
 *
 * Class LogHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class LogHelper extends AbstractHelper
{
    use CatalogLoggablesTrait,
        OffersLoggablesTrait,
        OrderLoggablesTrait,
        LogLoggablesTrait,
        ShipmentExportLoggablesTrait;

    /**
     * @var SettingsHelper
     */
    protected $_settingsHelper;

    /**
     * @var LogModelFactory
     */
    protected $_logFactory;

    /**
     * @var LogModelResource
     */
    protected $_logResource;

    /**
     * LogHelper constructor.
     *
     * @param Context $context
     * @param SettingsHelper $settingsHelper
     * @param LogModelFactory $logFactory
     * @param LogModelResource $logResource
     */
    public function __construct(
        Context $context,
        SettingsHelper $settingsHelper,
        LogModelFactory $logFactory,
        LogModelResource $logResource
    ) {
        parent::__construct($context);

        $this->_settingsHelper  = $settingsHelper;
        $this->_logFactory      = $logFactory;
        $this->_logResource     = $logResource;
    }

    /**
     * Get the full log.
     *
     * @return LogModelResourceCollection
     */
    public function getLog() : LogModelResourceCollection
    {
        $logModel       = $this->_logFactory->create();
        $logCollection  = $logModel->getCollection();

        return $logCollection;
    }

    /**
     * Get a log item by ID.
     * Returns null when not found.
     *
     * @param int $id
     * @return LogModel|DataObject|null
     */
    public function getLogItem(int $id)
    {
        $logCollection  = $this->getLog();
        $logItem        = $logCollection->getItemById($id);

        return $logItem;
    }

    /**
     * Get all loggables.
     *
     * @return Loggable[]
     */
    public function getLoggables() : array
    {
        $logCollection          = $this->getLog();
        $loggables              = [];

        foreach ($logCollection->getItems() as $logItem) {
            $loggable           = $this->getLoggableFromModel($logItem);
            if (!is_null($loggable)) {
                $loggables[]    = $loggable;
            }
        }

        return $loggables;
    }

    /**
     * Get a loggable by ID.
     * Returns null when not found.
     *
     * @param int $id
     * @return Loggable|null
     */
    public function getLoggable(int $id)
    {
        $logItem        = $this->getLogItem($id);

        return $logItem ? $this->getLoggableFromModel($logItem) : null;
    }

    /**
     * Cleans the log according to the configured expiration period.
     * Returns the number of removed entries.
     *
     * @return int
     */
    public function cleanLog() : int
    {
        $logExpirationString            = $this->_settingsHelper->getLogExpiration(SettingsHelper::SCOPE_STORE, 0);

        if (!LogExpiration::isValid($logExpirationString)) {
            $logExpiration              = LogExpiration::THREE_DAYS();
        } else {
            $logExpiration              = new LogExpiration($logExpirationString);
        }

        $logCollection                  = $this->getLog();

        $counter                        = 0;

        foreach ($logCollection->getItems() as $item) {
            $occurredAtString           = $item->getData('occurred_at');
            $occurredAt                 = intval(strtotime($occurredAtString ?? '1970-01-01 00:00:00') ?: 0);

            if ($logExpiration->isExpired($occurredAt)) {
                $item->delete();
                $counter++;
            }
        }

        if ($counter > 0) {
            // Activate line below to log if the log cleaning functionality works.
            // $this->logLogCleaned($counter);
        }

        return $counter;
    }

    /**
     * Insert a log entry to the database.
     * Returns whether inserting has succeeded.
     *
     * @param Loggable $loggable
     * @return bool
     */
    public function insertLogItem(Loggable $loggable) : bool
    {
        $logModel                       = $this->_logFactory->create();

        $logData                        = [
            'type'                      => $loggable->getType(),
            'code'                      => $loggable->getCode() ?? '',
            'process'                   => $loggable->getProcess() ?? '',
            'message'                   => $loggable->getMessage(),
            'payload'                   => $loggable->getPayload(),
            'occurred_at'               => $loggable->getOccurredAt(),
        ];

        if ($loggable->getConnectionId() !== 0) {
            $logData['connection_id']   = $loggable->getConnectionId();
        }

        if (
            $loggable->getSubjectId()   !== 0 &&
            !is_null($loggable->getSubjectType())
        ) {
            $logData['subject_type']    = $loggable->getSubjectType();
            $logData['subject_id']      = $loggable->getSubjectId();
        }

        $logModel->setData($logData);

        try {
            $this->_logResource->save($logModel);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get a log item by ID.
     * Returns null when not found.
     *
     * @param LogModel|DataObject $model
     * @return Loggable|null
     */
    protected function getLoggableFromModel($model)
    {
        $connectionId = $model->getConnectionId() ? intval($model->getConnectionId()) : 0;

        if (
            !LogType::isValid($model->getType()) ||
            !LogCode::isValid($model->getCode()) ||
            !Process::isValid($model->getProcess()) ||
            $connectionId === 0
        ) {
            return null;
        }

        $loggable   = new Loggable(
            (new LogType($model->getType())),
            (new LogCode($model->getCode())),
            (new Process($model->getProcess())),
            $connectionId
        );

        $loggable->setId($model->getEntityId() ? intval($model->getEntityId()) : 0);
        $loggable->setMessage($model->getMessage() ? strval($model->getMessage()) : '-');
        $loggable->setPayload($model->getPayload() ? strval($model->getPayload()) : '{}');
        $loggable->setOccurredAt($model->getOccurredAt() ? intval($model->getOccurredAt()) : 0);

        $subjectId = $model->getSubjectId() ? intval($model->getSubjectId()) : 0;
        if (
            LogSubjectType::isValid($model->getSubjectType()) &&
            $subjectId !== 0
        ) {
            $loggable->setSubject(
                new LogSubjectType($model->getSubjectType()),
                $subjectId
            );
        }

        return $loggable;
    }
}
