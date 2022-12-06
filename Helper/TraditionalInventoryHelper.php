<?php

namespace EffectConnect\Marketplaces\Helper;

use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockStateInterface;
use Throwable;

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
     * @var ProductLinkManagementInterface
     */
    protected $_productLinkManagement;

    /**
     * InventoryHelper Constructor
     *
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param StockStateInterface $stockState
     * @param ProductLinkManagementInterface $productLinkManagement
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        StockStateInterface $stockState,
        ProductLinkManagementInterface $productLinkManagement
    ) {
        parent::__construct($context);

        $this->_productRepository     = $productRepository;
        $this->_stockState            = $stockState;
        $this->_productLinkManagement = $productLinkManagement;
    }

    /**
     * @param ProductInterface $product
     * @param int $websiteId
     * @return float
     */
    public function getProductStockQuantity(ProductInterface $product, int $websiteId) : float
    {
        if ($product->getTypeId() === Type::TYPE_BUNDLE) {
            return $this->getBundleStockQuantity($product, $websiteId);
        }

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

    /**
     * In case of bundles the stock is determined by getting the lowest stock of the individual items in the bundle.
     * If there are multiple identical products in the bundle, the stock of that product is divided by the count.
     *
     * @param ProductInterface $product
     * @param int $websiteId
     * @return float
     */
    protected function getBundleStockQuantity(ProductInterface $product, int $websiteId) : float
    {
        $sku        = $product->getSku();
        $stocks     = [];
        $quantities = [];
        try {
            $children = $this->_productLinkManagement->getChildren($sku);
            foreach ($children as $child) {
                $childProduct = $this->_productRepository->getById($child->getEntityId());
                $stock = $this->getProductStockQuantity($childProduct, $websiteId);
                if (isset($quantities[$child->getEntityId()])) {
                    $quantities[$child->getEntityId()] += $child->getQty();
                } else {
                    $quantities[$child->getEntityId()] = $child->getQty();
                }
                $stocks[$child->getEntityId()] = floor($stock / $quantities[$child->getEntityId()]);
            }
        } catch (Throwable $e) {
            return 0;
        }

        return count($stocks) > 0 ? floatval(min($stocks)) : 0;
    }
}
