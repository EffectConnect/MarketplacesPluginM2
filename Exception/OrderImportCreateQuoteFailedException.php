<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when creating quote failed when importing order to Magento.
 *
 * Class OrderImportCreateQuoteFailedException
 * @package EffectConnect\Marketplaces\Exception
 */
class OrderImportCreateQuoteFailedException extends StateException
{
}