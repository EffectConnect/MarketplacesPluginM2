<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when adding payment and shipping methods to quote failed when importing order to Magento.
 *
 * Class OrderImportAddPaymentAndShippingMethodsToQuoteFailedException
 * @package EffectConnect\Marketplaces\Exception
 */
class OrderImportAddPaymentAndShippingMethodsToQuoteFailedException extends StateException
{
}