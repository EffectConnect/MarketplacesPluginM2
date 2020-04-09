<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml\Connection;

use EffectConnect\Marketplaces\Controller\Adminhtml\Connection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;

/**
 * Class Edit
 * @package EffectConnect\Marketplaces\Controller\Adminhtml\Connection
 */
class Edit extends Connection
{
    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $connectionId = $this->getRequest()->getParam('entity_id');
        $title = __('Add Connection');

        if ($connectionId) {
            try {
                $connection = $this->_connectionRepository->getById($connectionId);
                $title = __('Edit connection %1', $connection->getName());
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This connection no longer exists.'));
                return $this->_redirect('*/*');
            }
        }

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
}
