<?php

namespace EffectConnect\Marketplaces\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * This helper class helps obtaining traditional stock information from Magento.
 *
 * Class StockHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class TraditionalInventoryHelper extends BaseInventoryHelper
{
    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var StockStateInterface
     */
    protected $_stockState;

    /**
     * InventoryHelper Constructor
     *
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param StockStateInterface $stockState
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        StockStateInterface $stockState
    ) {
        parent::__construct($context);

        $this->_productRepository    = $productRepository;
        $this->_stockState           = $stockState;
    }

    /**
     * @param string $productSku
     * @param int $websiteId
     * @return float
     * @throws NoSuchEntityException
     */
    public function getProductStockQuantity(string $productSku, int $websiteId) : float
    {
        return $this->getDefaultStockQuantityBySku($productSku);
    }

    /**
     * @param int $entityId
     * @param int $websiteId
     * @return float
     * @throws NoSuchEntityException
     */
    public function getProductStockQuantityById(int $entityId, int $websiteId) : float
    {
        return $this->getProductStockQuantity($this->_productRepository->getById($entityId)->getSku(), $websiteId);
    }

    /**
     * @param string $productSku
     * @return float
     * @throws NoSuchEntityException
     */
    protected function getDefaultStockQuantityBySku(string $productSku) : float
    {
        $product    = $this->_productRepository->get($productSku);
        $quantity   = $this->_stockState->getStockQty($product->getId());

        return $quantity;
    }
}
