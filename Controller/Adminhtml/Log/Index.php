<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml\Log;

use EffectConnect\Marketplaces\Controller\Adminhtml\Log;

/**
 * Class Index
 * @package EffectConnect\Marketplaces\Controller\Adminhtml\Log
 */
class Index extends Log
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('EffectConnect_Marketplaces::marketplaces_logs');

        $resultPage->getConfig()
            ->getTitle()
            ->prepend((__('EffectConnect Marketplaces') . ' - ' . __('Log')));

        return $resultPage;
    }
}
