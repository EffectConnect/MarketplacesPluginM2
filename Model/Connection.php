<?php

namespace EffectConnect\Marketplaces\Model;

use EffectConnect\Marketplaces\Api\ChannelRepositoryInterface;
use EffectConnect\Marketplaces\Api\ConnectionStoreviewRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Class Connection
 * @method string|null getIsActive()
 * @method string|null getName()
 * @method string|null getPublicKey()
 * @method string|null getSecretKey()
 * @method string|null getWebsiteId()
 * @method string|null getImageUrlStoreviewId()
 * @method Connection setIsActive(string|null $string)
 * @method Connection setName(string|null $string)
 * @method Connection setPublicKey(string|null $string)
 * @method Connection setSecretKey(string|null $string)
 * @method Connection setWebsiteId(string|null $string)
 * @method Connection setImageUrlStoreviewId(string|null $string)
 * @package EffectConnect\Marketplaces\Model
 */
class Connection extends AbstractModel
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var ConnectionStoreviewRepositoryInterface
     */
    protected $_connectionStoreviewRepository;

    /**
     * @var ChannelRepositoryInterface
     */
    protected $_channelRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ConnectionStoreviewRepositoryInterface $connectionStoreViewRepository
     * @param ChannelRepositoryInterface $channelRepository
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection,
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ConnectionStoreviewRepositoryInterface $connectionStoreViewRepository,
        ChannelRepositoryInterface $channelRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_searchCriteriaBuilder         = $searchCriteriaBuilder;
        $this->_connectionStoreviewRepository = $connectionStoreViewRepository;
        $this->_channelRepository             = $channelRepository;
        return parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Connection::class);
    }

    /**
     * @return ExtensibleDataInterface[]
     */
    public function getStoreViews()
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter('connection_id', $this->getEntityId())
            ->create();

        return $this->_connectionStoreviewRepository
            ->getList($searchCriteria)
            ->getItems();
    }

    /**
     * @return ExtensibleDataInterface[]
     */
    public function getChannels()
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter('connection_id', $this->getEntityId())
            ->create();

        return $this->_channelRepository
            ->getList($searchCriteria)
            ->getItems();
    }
}
