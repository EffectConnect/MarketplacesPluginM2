<?php

namespace EffectConnect\Marketplaces\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
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
     * @param ProductInterface $product
     * @param int $websiteId
     * @return float
     */
    public function getProductStockQuantity(ProductInterface $product, int $websiteId) : float
    {
        return $this->getDefaultStockQuantity($product);
    }

    /**
     * @param ProductInterface $product
     * @return float
     */
    protected function getDefaultStockQuantity(ProductInterface $product) : float
    {
        return $this->_stockState->getStockQty($product->getId());
    }
}
