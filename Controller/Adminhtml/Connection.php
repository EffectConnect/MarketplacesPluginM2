<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml;

use EffectConnect\Marketplaces\Api\ConnectionRepositoryInterface;
use EffectConnect\Marketplaces\Api\ConnectionStoreviewRepositoryInterface;
use EffectConnect\Marketplaces\Helper\ApiHelper;
use EffectConnect\Marketplaces\Objects\QueueHandlers\DirectCatalogExportQueueHandler;
use EffectConnect\Marketplaces\Objects\QueueHandlers\LogExportQueueHandler;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action\Context;

/**
 * Class Connection
 * @package EffectConnect\Marketplaces\Controller\Adminhtml
 */
abstract class Connection extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var ConnectionRepositoryInterface
     */
    protected $_connectionRepository;

    /**
     * @var ConnectionStoreviewRepositoryInterface
     */
    protected $_connectionStoreviewRepository;

    /**
     * @var DirectCatalogExportQueueHandler
     */
    protected $_directCatalogExportQueueHandler;

    /**
     * @var LogExportQueueHandler
     */
    protected $_logExportQueueHandler;

    /**
     * Connection constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param ApiHelper $apiHelper
     * @param ConnectionRepositoryInterface $connectionRepository
     * @param ConnectionStoreviewRepositoryInterface $connectionStoreviewRepository
     * @param DirectCatalogExportQueueHandler $directCatalogExportQueueHandler
     * @param LogExportQueueHandler $logExportQueueHandler
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        ApiHelper $apiHelper,
        ConnectionRepositoryInterface $connectionRepository,
        ConnectionStoreviewRepositoryInterface $connectionStoreviewRepository,
        DirectCatalogExportQueueHandler $directCatalogExportQueueHandler,
        LogExportQueueHandler $logExportQueueHandler
    ) {
        parent::__construct($context);
        $this->_resultPageFactory               = $resultPageFactory;
        $this->_resultJsonFactory               = $resultJsonFactory;
        $this->_apiHelper                       = $apiHelper;
        $this->_connectionRepository            = $connectionRepository;
        $this->_connectionStoreviewRepository   = $connectionStoreviewRepository;
        $this->_directCatalogExportQueueHandler = $directCatalogExportQueueHandler;
        $this->_logExportQueueHandler           = $logExportQueueHandler;
    }
}
