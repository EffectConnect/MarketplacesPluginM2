<?php
namespace EffectConnect\Marketplaces\Controller\Adminhtml\Redirect;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Marketplaces
 * @package EffectConnect\Marketplaces\Controller\Adminhtml
 */
class Marketplaces extends Action
{
    /**
     * The GitHub endpoint URL for obtaining the latest release data.
     */
    const EFFECTCONNECT_MARKETPLACES_URL = "https://go.effectconnect.com/";

    /**
     * @var ResultFactory
     */
    protected $_resultFactory;

    /**
     * Login Constructor
     *
     * @param Context $context
     * @param ResultFactory $resultFactory
     */
    public function __construct(Context $context, ResultFactory $resultFactory)
    {
        parent::__construct($context);

        $this->_resultFactory = $resultFactory;
    }

    /**
     * Redirect to EffectConnect Marketplaces Go.
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $redirect->setUrl(static::EFFECTCONNECT_MARKETPLACES_URL);
        
        return $redirect;
    }
}