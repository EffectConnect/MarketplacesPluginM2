<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when setting currency to quote when importing order to Magento.
 *
 * Class OrderImportAddCurrencyToQuoteFailedException
 * @package EffectConnect\Marketplaces\Exception
 */
class OrderImportAddCurrencyToQuoteFailedException extends StateException
{
}