<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when importing orders from EffectConnect to Magento.
 *
 * Class OrderImportFailedException
 * @package EffectConnect\Marketplaces\Exception
 */
class OrderImportFailedException extends StateException { }