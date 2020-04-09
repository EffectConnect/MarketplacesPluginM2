<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml\Connection;

use EffectConnect\Marketplaces\Controller\Adminhtml\Connection;
use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Delete
 * @package EffectConnect\Marketplaces\Controller\Adminhtml\Connection
 */
class Delete extends Connection
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                $this->_connectionRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The connection has been deleted.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('The connection could not been deleted.')
                );
            }
        }
        $this->_redirect('*/*');
    }
}
