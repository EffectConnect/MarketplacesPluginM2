<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when transforming a product option that has no valid EAN.
 *
 * Class CatalogExportEanNotValidException
 * @package EffectConnect\Marketplaces\Exception
 */
class CatalogExportEanNotValidException extends StateException { }