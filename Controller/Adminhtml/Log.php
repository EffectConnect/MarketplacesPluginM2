<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use EffectConnect\Marketplaces\Model\LogFactory as LogModelFactory;
use EffectConnect\Marketplaces\Model\ResourceModel\Log as LogModelResource;

/**
 * Class Log
 * @package EffectConnect\Marketplaces\Controller\Adminhtml
 */
abstract class Log extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var LogModelFactory
     */
    protected $_logFactory;

    /**
     * @var LogModelResource
     */
    protected $_logResource;

    /**
     * Log constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param LogModelFactory $logFactory
     * @param LogModelResource $logResource
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LogModelFactory $logFactory,
        LogModelResource $logResource
    ) {
        parent::__construct($context);
        $this->_resultPageFactory   = $resultPageFactory;
        $this->_logFactory          = $logFactory;
        $this->_logResource         = $logResource;
    }
}
