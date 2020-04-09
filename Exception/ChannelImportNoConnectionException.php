<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when channel import failed because there are no connections.
 *
 * Class ChannelImportNoConnectionException
 * @package EffectConnect\Marketplaces\Exception
 */
class ChannelImportNoConnectionException extends StateException
{
}