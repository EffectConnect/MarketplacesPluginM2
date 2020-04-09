<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when adding product to quote failed when importing order to Magento.
 *
 * Class OrderImportAddProductsToQuoteFailedException
 * @package EffectConnect\Marketplaces\Exception
 */
class OrderImportAddProductsToQuoteFailedException extends StateException
{
}