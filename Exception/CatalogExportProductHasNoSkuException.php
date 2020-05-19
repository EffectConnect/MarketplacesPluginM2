<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when obtaining stock from a product that has no SKU.
 *
 * Class CatalogExportProductHasNoSkuException
 * @package EffectConnect\Marketplaces\Exception
 */
class CatalogExportProductHasNoSkuException extends StateException { }