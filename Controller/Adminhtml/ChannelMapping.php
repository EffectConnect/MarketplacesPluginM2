<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml;

use EffectConnect\Marketplaces\Api\ChannelRepositoryInterface;
use EffectConnect\Marketplaces\Api\ConnectionRepositoryInterface;
use EffectConnect\Marketplaces\Api\ChannelMappingRepositoryInterface;
use EffectConnect\Marketplaces\Helper\ApiHelper;
use EffectConnect\Marketplaces\Helper\ChannelMappingHelper;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;

/**
 * Class Connection
 * @package EffectConnect\Marketplaces\Controller\Adminhtml
 */
abstract class ChannelMapping extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var ChannelMappingRepositoryInterface
     */
    protected $_channelMappingRepository;

    /**
     * @var ConnectionRepositoryInterface
     */
    protected $_connectionRepository;

    /**
     * @var ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var ChannelRepositoryInterface
     */
    protected $_channelRepository;

    /**
     * @var ChannelMappingHelper
     */
    protected $_channelMappingHelper;

    /**
     * ChannelMapping constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ChannelMappingRepositoryInterface $channelMappingRepository
     * @param ChannelRepositoryInterface $channelRepository
     * @param ConnectionRepositoryInterface $connectionRepository
     * @param ApiHelper $apiHelper
     * @param ChannelMappingHelper $channelMappingHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ChannelMappingRepositoryInterface $channelMappingRepository,
        ChannelRepositoryInterface $channelRepository,
        ConnectionRepositoryInterface $connectionRepository,
        ApiHelper $apiHelper,
        ChannelMappingHelper $channelMappingHelper
    )
    {
        parent::__construct($context);
        $this->_resultPageFactory        = $resultPageFactory;
        $this->_channelMappingRepository = $channelMappingRepository;
        $this->_channelRepository        = $channelRepository;
        $this->_connectionRepository     = $connectionRepository;
        $this->_apiHelper                = $apiHelper;
        $this->_channelMappingHelper     = $channelMappingHelper;
    }
}
