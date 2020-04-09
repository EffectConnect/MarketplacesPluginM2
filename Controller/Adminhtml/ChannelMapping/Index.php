<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping;

use EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

/**
 * Class Index
 * @package EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping
 */
class Index extends ChannelMapping
{
    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('EffectConnect_Marketplaces::marketplaces_channel_mapping');
        $resultPage->getConfig()->getTitle()->prepend(((__('EffectConnect Marketplaces') . ' - ' . __('Channel mapping'))));
        return $resultPage;
    }
}
