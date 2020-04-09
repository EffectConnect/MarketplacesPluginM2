<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when adding customer to quote failed when importing order to Magento.
 *
 * Class OrderImportAddCustomerToQuoteFailedException
 * @package EffectConnect\Marketplaces\Exception
 */
class OrderImportAddCustomerToQuoteFailedException extends StateException
{
}