<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping;

use EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping;
use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Delete
 * @package EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping
 */
class Delete extends ChannelMapping
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                $this->_channelMappingRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The channel mapping has been deleted.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('The channel mapping could not been deleted.')
                );
            }
        }
        $this->_redirect('*/*');
    }
}
