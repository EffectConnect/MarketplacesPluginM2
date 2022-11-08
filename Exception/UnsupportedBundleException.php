<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when unsupported bundle is exported or imported.
 * Currently only bundle products with exactly one option for each bundle item are supported.
 *
 * Class UnsupportedBundleException
 * @package EffectConnect\Marketplaces\Exception
 */
class UnsupportedBundleException extends StateException { }