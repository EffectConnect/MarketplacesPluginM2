<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when submitting quote failed when importing order to Magento.
 *
 * Class OrderImportSubmitQuoteFailedException
 * @package EffectConnect\Marketplaces\Exception
 */
class OrderImportSubmitQuoteFailedException extends StateException { }