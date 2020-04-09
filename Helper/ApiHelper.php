<?php

namespace EffectConnect\Marketplaces\Helper;

use EffectConnect\Marketplaces\Api\ConnectionRepositoryInterface;
use EffectConnect\Marketplaces\Model\Connection;
use EffectConnect\Marketplaces\Objects\ConnectionApi;
use EffectConnect\PHPSdk\Core\Exception\InvalidKeyException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * This helper class helps creating and obtaining the API instances for each connection.
 *
 * Class ApiHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class ApiHelper extends AbstractHelper
{
    /**
     * @var array
     */
    protected $_instances;

    /**
     * @var ConnectionRepositoryInterface
     */
    protected $_connectionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilderFactory;

    /**
     * @var TransformerHelper
     */
    protected $_transformerHelper;

    /**
     * @var LogHelper
     */
    protected $_logHelper;

    /**
     * LogHelper constructor.
     *
     * @param Context $context
     * @param ConnectionRepositoryInterface $connectionRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param TransformerHelper $transformerHelper
     * @param LogHelper $logHelper
     */
    public function __construct(
        Context $context,
        ConnectionRepositoryInterface $connectionRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        TransformerHelper $transformerHelper,
        LogHelper $logHelper
    ) {
        parent::__construct($context);

        $this->_connectionRepository                = $connectionRepository;
        $this->_searchCriteriaBuilderFactory        = $searchCriteriaBuilderFactory;
        $this->_transformerHelper                   = $transformerHelper;
        $this->_logHelper                           = $logHelper;

        $this->instantiateConnectionApis();
    }

    /**
     * Instantiate an Api instance for each active connection.
     */
    protected function instantiateConnectionApis()
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder                      = $this->_searchCriteriaBuilderFactory->create();
        $searchCriteria                             = $searchCriteriaBuilder->addFilter('is_active', true)->create();
        $connectionCollection                       = $this->_connectionRepository->getList($searchCriteria);
        $this->_instances                           = [];

        /** @var Connection $connection */
        foreach ($connectionCollection->getItems() as $connection) {
            $connectionId                           = $connection->getEntityId();
            try {
                $instance                           = new ConnectionApi($connection, $this, $this->_logHelper);
                $this->_instances[$connectionId]    = $instance;
            } catch (InvalidKeyException $e) {
                continue;
            }
        }
    }

    /**
     * @param int $connectionId
     * @return ConnectionApi
     * @throws NoSuchEntityException
     * @throws InvalidKeyException
     */
    public function getConnectionApi(int $connectionId) : ConnectionApi
    {
        if (!isset($this->_instances[$connectionId])) {
            $connection                             = $this->_connectionRepository->getById($connectionId);
            $instance                               = new ConnectionApi($connection, $this, $this->_logHelper);
            $this->_instances[$connectionId]        = $instance;
        }
        return $this->_instances[$connectionId];
    }

    /**
     * @return TransformerHelper
     */
    public function getTransformerHelper() : TransformerHelper
    {
        return $this->_transformerHelper;
    }

    /**
     * @param $publicKey
     * @param $secretKey
     * @return bool
     */
    public function testCredentials($publicKey, $secretKey)
    {
        if (!empty($publicKey) && !empty($secretKey)) {
            try {
                $connection = $this->_connectionRepository->create();
                $connection->setPublicKey($publicKey);
                $connection->setSecretKey($secretKey);
                new ConnectionApi($connection, $this, $this->_logHelper);
                return true;
            } catch (InvalidKeyException $e) {}
        }
        return false;
    }
}
