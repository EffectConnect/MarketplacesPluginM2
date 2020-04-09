<?php

namespace EffectConnect\Marketplaces\Objects\QueueHandlers;

use EffectConnect\Marketplaces\Api\ConnectionRepositoryInterface;
use EffectConnect\Marketplaces\Api\LogExportQueueItemRepositoryInterface;
use EffectConnect\Marketplaces\Api\LogRepositoryInterface;
use EffectConnect\Marketplaces\Exception\LogExportQueueFailedException;
use EffectConnect\Marketplaces\Helper\ApiHelper;
use EffectConnect\Marketplaces\Helper\LogHelper;
use EffectConnect\Marketplaces\Helper\TransformerHelper;
use EffectConnect\Marketplaces\Interfaces\QueueHandlerInterface;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class LogExportQueueHandler
 * @package EffectConnect\Marketplaces\Objects\QueueHandlers
 */
class LogExportQueueHandler implements QueueHandlerInterface
{
    /**
     * @var ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var LogHelper
     */
    protected $_logHelper;

    /**
     * @var ConnectionRepositoryInterface
     */
    protected $_connectionRepository;

    /**
     * @var LogExportQueueItemRepositoryInterface
     */
    protected $_logExportQueueItemRepository;

    /**
     * @var LogRepositoryInterface
     */
    protected $_logRepositoryInterface;

    /**
     * LogExportQueueHandler constructor.
     *
     * @param ApiHelper $apiHelper
     * @param LogHelper $logHelper
     * @param ConnectionRepositoryInterface $connectionRepository
     * @param LogExportQueueItemRepositoryInterface $logExportQueueItemRepository
     * @param LogRepositoryInterface $logRepositoryInterface
     */
    public function __construct(
        ApiHelper $apiHelper,
        LogHelper $logHelper,
        ConnectionRepositoryInterface $connectionRepository,
        LogExportQueueItemRepositoryInterface $logExportQueueItemRepository,
        LogRepositoryInterface $logRepositoryInterface
    ) {
        $this->_apiHelper                    = $apiHelper;
        $this->_logHelper                    = $logHelper;
        $this->_connectionRepository         = $connectionRepository;
        $this->_logExportQueueItemRepository = $logExportQueueItemRepository;
        $this->_logRepositoryInterface       = $logRepositoryInterface;
    }

    /**
     * @param int $connectionId
     * @throws LogExportQueueFailedException
     */
    public function schedule(int $connectionId)
    {
        if ($this->_logExportQueueItemRepository->isNonExecutedConnectionPresent($connectionId)) {
            throw new LogExportQueueFailedException(__('Unable to save new LogExportQueueItem entry, because another queue item already exists.'));
        }

        // Get the connection to export the log to.
        try {
            $connectionApi = $this->_apiHelper->getConnectionApi($connectionId);
        } catch (Exception $e) {
            throw new LogExportQueueFailedException(__('Could not create connection. Error: %1', $e->getMessage()));
        }

        // Check if we are allowed to send the log.
        $isLogExportAllowed = $connectionApi->isExportLogAllowed();
        if (!$isLogExportAllowed) {
            throw new LogExportQueueFailedException(__('Exporting log is not allowed for current connection. Please contact support to export the log.'));
        }

        // Add the connection to the queue.
        try {
            $logExportQueueItem = $this->_logExportQueueItemRepository->create();
            $logExportQueueItem->setConnectionEntityId($connectionId);
            $this->_logExportQueueItemRepository->save($logExportQueueItem);
        } catch (CouldNotSaveException $e) {
            throw new LogExportQueueFailedException(__('Unable to save new LogExportQueueItem entry. Error: %1', $e->getMessage()));
        }
    }

    /**
     * @return void
     */
    public function execute()
    {
        $queue = $this->_logExportQueueItemRepository->getListUnexecuted();
        if ($queue->getTotalCount() > 0)
        {
            foreach ($queue->getItems() as $logToExportQueueItem)
            {
                $connectionId = intval($logToExportQueueItem->getConnectionEntityId());

                // Get the connection to export the log to.
                try {
                    $connectionApi = $this->_apiHelper->getConnectionApi($connectionId);
                } catch (Exception $e) {
                     $this->_logHelper->logExportConnectionError($connectionId, $e->getMessage());
                    continue;
                }

                // Check if we are allowed to send the log.
                $isLogExportAllowed = $connectionApi->isExportLogAllowed();
                if (!$isLogExportAllowed) {
                    continue;
                }

                // Save that we are exporting this log to prevent other cronjobs to process the same item.
                // Bad luck if the export fails, we will not try to do this again.
                $logToExportQueueItem->setExecutedAt(time());
                try {
                    $this->_logExportQueueItemRepository->save($logToExportQueueItem);
                } catch (CouldNotSaveException $e) {
                    $this->_logHelper->logExportSaveError($connectionId, $e->getMessage());
                    continue;
                }

                // Export the log items.
                $connectionApi->exportLog();
            }
        }
    }
}