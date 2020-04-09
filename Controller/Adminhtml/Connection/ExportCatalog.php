<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml\Connection;

use Exception;
use EffectConnect\Marketplaces\Controller\Adminhtml\Connection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class ExportCatalog
 * @package EffectConnect\Marketplaces\Controller\Adminhtml\Connection
 */
class ExportCatalog extends Connection
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        if ($id && intval($id) > 0) {
            try {
                $this->_directCatalogExportQueueHandler->schedule($id);
                $this->messageManager->addSuccessMessage(__("The connection's direct catalog export has been added to the queue. The queue gets executed every minute."));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __("The connection's direct catalog export could not be added to the queue.")
                );
            }
        }
        $this->_redirect('*/*');
    }
}
