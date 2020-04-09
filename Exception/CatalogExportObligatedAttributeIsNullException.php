<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when setting an attribute to the output product that is obligated but empty or null.
 *
 * Class CatalogExportObligatedAttributeIsNullException
 * @package EffectConnect\Marketplaces\Exception
 */
class CatalogExportObligatedAttributeIsNullException extends StateException { }