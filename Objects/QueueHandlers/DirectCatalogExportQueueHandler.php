<?php

namespace EffectConnect\Marketplaces\Objects\QueueHandlers;

use EffectConnect\Marketplaces\Api\ConnectionRepositoryInterface;
use EffectConnect\Marketplaces\Api\DirectCatalogExportQueueItemRepositoryInterface;
use EffectConnect\Marketplaces\Helper\ApiHelper;
use EffectConnect\Marketplaces\Helper\LogHelper;
use EffectConnect\Marketplaces\Interfaces\QueueHandlerInterface;
use EffectConnect\Marketplaces\Model\DirectCatalogExportQueueItem;
use EffectConnect\PHPSdk\Core\Exception\InvalidKeyException;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class DirectCatalogExportQueueHandler
 * @package EffectConnect\Marketplaces\Objects\QueueHandlers
 */
class DirectCatalogExportQueueHandler implements QueueHandlerInterface
{
    /**
     * @var ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var ConnectionRepositoryInterface
     */
    protected $_connectionRepository;

    /**
     * @var DirectCatalogExportQueueItemRepositoryInterface
     */
    protected $_directCatalogExportQueueItemRepository;

    /**
     * @var LogHelper
     */
    protected $_logHelper;

    /**
     * DirectCatalogExportQueueHandler constructor.
     *
     * @param ApiHelper $apiHelper
     * @param ConnectionRepositoryInterface $connectionRepository
     * @param DirectCatalogExportQueueItemRepositoryInterface $directCatalogExportQueueItemRepository
     * @param LogHelper $logHelper
     */
    public function __construct(
        ApiHelper $apiHelper,
        ConnectionRepositoryInterface $connectionRepository,
        DirectCatalogExportQueueItemRepositoryInterface $directCatalogExportQueueItemRepository,
        LogHelper $logHelper
    ) {
        $this->_apiHelper                               = $apiHelper;
        $this->_connectionRepository                    = $connectionRepository;
        $this->_directCatalogExportQueueItemRepository  = $directCatalogExportQueueItemRepository;
        $this->_logHelper                               = $logHelper;
    }

    /**
     * Schedule an item to be picked up by the queue handler.
     *
     * @param int $connectionId
     * @return void
     * @throws CouldNotSaveException
     * @throws Exception
     */
    public function schedule(int $connectionId)
    {
        if ($this->_directCatalogExportQueueItemRepository->isNonExecutedConnectionPresent($connectionId)) {
            return;
        }

        $directCatalogExportQueueItem   = $this->_directCatalogExportQueueItemRepository->create();
        $connection                     = $this->_connectionRepository->getById($connectionId);

        $directCatalogExportQueueItem->setConnectionEntityId(intval($connection->getEntityId()));
        $this->_directCatalogExportQueueItemRepository->save($directCatalogExportQueueItem);
    }

    /**
     * Execute all queued items.
     *
     * @return void
     */
    public function execute()
    {
        $oldest = $this->_directCatalogExportQueueItemRepository->getOldestUnexecuted();

        while ($oldest->getTotalCount() > 0) {
            /** @var DirectCatalogExportQueueItem $directCatalogExportQueueItem */
            foreach ($oldest->getItems() as $directCatalogExportQueueItem) {
                $directCatalogExportQueueItem->setExecutedAt(time());

                try {
                    $this->_directCatalogExportQueueItemRepository->save($directCatalogExportQueueItem);
                } catch (Exception $e) {
                    continue;
                }

                $connectionId = intval($directCatalogExportQueueItem->getConnectionEntityId());

                try {
                    $api = $this->_apiHelper->getConnectionApi($connectionId);
                    if (!is_null($api)) {
                        $api->exportCatalog();
                    }
                } catch (NoSuchEntityException $e) {
                    $this->_logHelper->logCatalogExportConnectionFailed($connectionId, [
                        'exception' => $e->getMessage()
                    ]);
                    continue;
                } catch (InvalidKeyException $e) {
                    $this->_logHelper->logCatalogExportConnectionFailed($connectionId, [
                        'exception' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            $oldest = $this->_directCatalogExportQueueItemRepository->getOldestUnexecuted();
        }
    }
}