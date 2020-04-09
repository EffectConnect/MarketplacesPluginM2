<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml\Connection;

use EffectConnect\Marketplaces\Controller\Adminhtml\Connection;
use EffectConnect\Marketplaces\Exception\LogExportQueueFailedException;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class ExportLog
 * @package EffectConnect\Marketplaces\Controller\Adminhtml\Connection
 */
class ExportLog extends Connection
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        if (intval($id) > 0)
        {
            try
            {
                $this->_logExportQueueHandler->schedule($id);
                $this->messageManager->addSuccessMessage(__('The log has been added to the export queue. The queue gets executed every minute.'));
            }
            catch (LogExportQueueFailedException $e)
            {
                $this->messageManager->addErrorMessage(
                    $e->getMessage()
                );
            }
        }
        $this->_redirect('*/*');
    }
}
