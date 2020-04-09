<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml\Connection;

use EffectConnect\Marketplaces\Controller\Adminhtml\Connection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

/**
 * Class Index
 * @package EffectConnect\Marketplaces\Controller\Adminhtml\Connection
 */
class Index extends Connection
{
    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('EffectConnect_Marketplaces::marketplaces_connections');
        $resultPage->getConfig()->getTitle()->prepend(((__('EffectConnect Marketplaces') . ' - ' . __('Connections'))));
        return $resultPage;
    }
}
