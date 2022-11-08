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
     *
     * @param ProductInterface $product
     * @param int $websiteId
     * @return float
     */
    protected function getBundleStockQuantity(ProductInterface $product, int $websiteId) : float
    {
        $minimumStock = null;
        $sku = $product->getSku();
        try {
            $children = $this->_productLinkManagement->getChildren($sku);
            foreach ($children as $child) {
                $childProduct = $this->_productRepository->getById($child->getEntityId());
                $stock = $this->getProductStockQuantity($childProduct, $websiteId);
                if ($minimumStock === null || $stock < $minimumStock) {
                    $minimumStock = $stock;
                }
            }
        } catch (Throwable $e) {
            return 0;
        }

        return floatval($minimumStock);
    }
}
