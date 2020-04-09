<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when saving connection failed because storeview mapping contains the same language code multiple times.
 *
 * Class ConnectionSaveDuplicateLanguageException
 * @package EffectConnect\Marketplaces\Exception
 */
class ConnectionSaveDuplicateLanguageException extends StateException
{
}