<?php

namespace EffectConnect\Marketplaces\Helper\Transformer;

use EffectConnect\Marketplaces\Api\LogRepositoryInterface;
use EffectConnect\Marketplaces\Enums\LogType;
use EffectConnect\Marketplaces\Helper\LogHelper;
use EffectConnect\Marketplaces\Helper\SettingsHelper;
use EffectConnect\Marketplaces\Helper\XmlGenerator;
use EffectConnect\Marketplaces\Interfaces\ValueType;
use DOMException;
use EffectConnect\Marketplaces\Model\Connection;
use EffectConnect\Marketplaces\Model\Log;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * The LogExportTransformer obtains the log from a certain connection.
 * Then it transforms that logs into a format that can be used with the EffectConnect Marketplaces SDK.
 *
 * Class LogExportTransformer
 * @package EffectConnect\Marketplaces\Helper\Transformer
 */
class LogExportTransformer extends AbstractHelper implements ValueType
{
    /**
     * The default products per page when obtaining the catalog.
     */
    const DEFAULT_PAGE_SIZE     = 50;

    /**
     * @var LogRepositoryInterface
     */
    protected $_logRepository;

    /**
     * @var SettingsHelper
     */
    protected $_settingsHelper;

    /**
     * @var LogHelper
     */
    protected $_logHelper;

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
     * @var Connection
     */
    protected $_connection;


    /**
     * Constructs the LogExportTransformer helper class.
     *
     * @param Context $context
     * @param LogRepositoryInterface $logRepository
     * @param SettingsHelper $settingsHelper
     * @param LogHelper $logHelper
     * @param Filesystem $filesystem
     * @param DirectoryList $directoryList
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        Context $context,
        LogRepositoryInterface $logRepository,
        SettingsHelper $settingsHelper,
        LogHelper $logHelper,
        Filesystem $filesystem,
        DirectoryList $directoryList,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        parent::__construct($context);
        $this->_logRepository                   = $logRepository;
        $this->_settingsHelper                  = $settingsHelper;
        $this->_logHelper                       = $logHelper;
        $this->_filesystem                      = $filesystem;
        $this->_directoryList                   = $directoryList;
        $this->_searchCriteriaBuilderFactory    = $searchCriteriaBuilderFactory;
    }


    /**
     * Transform and write log to EffectConnect Marketplaces desired XML format (segmented per log item).
     *
     * @param Connection $connection
     * @return bool|string
     */
    public function saveXmlSegmented(Connection $connection)
    {
        $this->_connection  = $connection;

        $directoryType      = 'var';
        $relativeFile       = 'effectconnect/marketplaces/export/log.xml';

        try {
            $fileLocation   = $this->_directoryList->getPath($directoryType) . '/' . $relativeFile;
        } catch (FileSystemException $e) {
            $this->_logHelper->logLogExportXmlFileCreationFailed(intval($connection->getEntityId()), '-');
            return false;
        }

        try {
            $transaction    = XmlGenerator::startMageStorageTransaction($this->_filesystem, $directoryType, $relativeFile, 'entries');
        } catch (DOMException $e) {
            $transaction    = false;
        } catch (FileSystemException $e) {
            $transaction    = false;
        }

        if (!$transaction) {
            $this->_logHelper->logLogExportXmlFileCreationFailed(intval($connection->getEntityId()), $fileLocation);

            return false;
        }

        $lastPage       = true;
        $currentPage    = 1;
        $itemsPerPage   = $this->getPageSize();

        do {
            $logItems   = $this->getConnectionLog($itemsPerPage, $currentPage, $lastPage);

            /** @var ProductInterface $product */
            foreach ($logItems as $logItem) {
                $this->saveLogItemXml($logItem, $connection, $transaction);
            }

            $currentPage++;
        } while ($lastPage === false);

        if (!$transaction->endMageStorageTransaction()) {
            $this->_logHelper->logLogExportXmlFileCreationFailed(intval($connection->getEntityId()), $fileLocation);
            return false;
        }

        return $fileLocation;
    }

    /**
     * Transform and write log item to EffectConnect Marketplaces desired XML format.
     *
     * @param Log $logItem
     * @param Connection $connection
     * @param XmlGenerator $transaction
     * @return void
     */
    protected function saveLogItemXml(Log $logItem, Connection $connection, XmlGenerator &$transaction)
    {
       $transformed    = $this->transformLogItem($logItem);

        if (is_null($transformed)) {
            return;
        }

        $success = false;

        try {
            if ($transaction->appendToMageStorageFile($transformed, 'entry')) {
                $success = true;
            }
        } catch (DOMException $e) {
            $success = false;
        }

        if (!$success) {
            $this->_logHelper->logLogExportXmlFileCreationFailed(intval($connection->getEntityId()), intval($logItem->getEntityId()) ?? 0);
            return;
        }
    }

    /**
     * Transform a certain log item to the EffectConnect Marketplaces SDK expected format.
     *
     * @param Log $logItem
     * @return array|null
     */
    protected function transformLogItem(Log $logItem)
    {
        $recordedAt     = $this->getLogRecordedAt($logItem);
        $type           = $this->getLogType($logItem);
        $code           = $this->getLogCode($logItem);
        $message        = $this->getLogMessage($logItem);

        $transformed    = [];

        $this->setValueToArray($transformed, 'recordedAt', $recordedAt, false);
        $this->setValueToArray($transformed, 'type', $type);
        $this->setValueToArray($transformed, 'code', $code);
        $this->setValueToArray($transformed, 'message', $message, true);

        return $transformed;
    }


    /**
     * Get the log item's recorded at in the EffectConnect Marketplaces SDK expected format.
     *
     * @param Log $log
     * @return string
     */
    protected function getLogRecordedAt(Log $log) : string
    {
        $input      = strval($log->getOccurredAt() ?? '');
        $timestamp  = strtotime($input);

        return date('Y-m-d\TH:i:sP', $timestamp) ?: date('Y-m-d\TH:i:sP');
    }

    /**
     * Get the log item's type in the EffectConnect Marketplaces SDK expected format.
     *
     * @param Log $log
     * @return string
     */
    protected function getLogType(Log $log) : string
    {
        switch ($log->getType()) {
            case LogType::ERROR:
            case LogType::FATAL:
                return 'Error';
            case LogType::WARNING:
                return 'Warning';
            case LogType::SUCCESS:
                return 'Success';
            case LogType::NOTICE:
            case LogType::INFO:
            default:
                return 'Notice';
        }
    }

    /**
     * Get the log item's code in the EffectConnect Marketplaces SDK expected format.
     *
     * @param Log $log
     * @return string
     */
    protected function getLogCode(Log $log) : string
    {
        return strval($log->getCode());
    }

    /**
     * Get the log item's message (JSON payload) in the EffectConnect Marketplaces SDK expected format.
     *
     * @param Log $log
     * @return string
     */
    protected function getLogMessage(Log $log) : string
    {
        return json_encode($log->getData());
    }


    /**
     * Get the log for a certain connection.
     *
     * @param int $itemsPerPage
     * @param int $page
     * @param bool $isLast
     * @return Log[]
     */
    protected function getConnectionLog(int $itemsPerPage = 0, int $page = 0, bool &$isLast = true) : array
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder  = $this->_searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder->addFilter(
            'connection_id',
            intval($this->_connection->getEntityId()),
            'eq'
        );

        if ($itemsPerPage !== 0 && $page !== 0) {
            $searchCriteriaBuilder
                ->setPageSize($itemsPerPage)
                ->setCurrentPage($page);
        }

        $searchCriteria         = $searchCriteriaBuilder->create();

        $log                    = $this->_logRepository->getList($searchCriteria);
        $logItems               = $log->getItems();

        if ($itemsPerPage !== 0 && $page !== 0) {
            $isLast             = $log->getTotalCount() <= $itemsPerPage * $page;
        } else {
            $isLast             = true;
        }

        return $logItems;
    }

    /**
     * Get the number of log items per page when obtaining the log items.
     *
     * @return int
     */
    protected function getPageSize() : int
    {
        $settingsPageSize = $this->_settingsHelper->getLogPageSize(SettingsHelper::SCOPE_WEBSITE, intval($this->_connection->getWebsiteId()));
        return intval($settingsPageSize ?? static::DEFAULT_PAGE_SIZE);
    }


    /**
     * Add a value to an array.
     *
     * @param array $array
     * @param $key
     * @param $value
     * @param bool $asCdataWhenString
     * @return void
     */
    protected function setValueToArray(array &$array, string $key, $value, bool $asCdataWhenString = true)
    {
        if (is_string($value) && $asCdataWhenString) {
            $array[$key] =  [
                '_cdata' => $value
            ];
        } else {
            $array[$key] = $value;
        }
    }
}
