<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping;

use EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;

/**
 * Class Edit
 * @package EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping
 */
class Edit extends ChannelMapping
{
    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $channelMappingId = $this->getRequest()->getParam('entity_id');
        $title = __('Add channel mapping');

        if ($channelMappingId) {
            try {
                $this->_channelMappingRepository->getById($channelMappingId);
                $title = __('Edit channel mapping');
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This channel mapping no longer exists.'));
                return $this->_redirect('*/*');
            }
        }

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
}
