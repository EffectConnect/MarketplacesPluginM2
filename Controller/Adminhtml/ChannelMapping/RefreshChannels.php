<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping;

use EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping;
use EffectConnect\Marketplaces\Enums\LogType;
use EffectConnect\Marketplaces\Exception\ChannelImportNoConnectionException;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class RefreshChannels
 * @package EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping
 */
class RefreshChannels extends ChannelMapping
{
    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        // For all connection fetch channels from EC API and save them in database.
        try
        {
            $this->_channelMappingHelper->refreshChannels();
        }
        catch (ChannelImportNoConnectionException $e)
        {
            $this->messageManager->addNoticeMessage($e->getMessage());
        }

        foreach ($this->_channelMappingHelper->getLoggables() as $loggable)
        {
            switch ($loggable->getType())
            {
                case LogType::SUCCESS():
                    $this->messageManager->addSuccessMessage($loggable->getMessage());
                    break;
                case LogType::ERROR():
                    $this->messageManager->addErrorMessage($loggable->getMessage());
                    break;
                default:
                    $this->messageManager->addNoticeMessage($loggable->getMessage());
            }
        }

        // Redirect to page the user come from.
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
