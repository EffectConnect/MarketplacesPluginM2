<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when no are no products to export.
 * This could be the case when product IDs are queued for offer export, but later it appears that they don't have to be exported at all.
 *
 * Class CatalogExportNoProductsToExportException
 * @package EffectConnect\Marketplaces\Exception
 */
class CatalogExportNoProductsToExportException extends StateException { }