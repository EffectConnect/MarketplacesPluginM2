<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when a cron expression is not valid.
 *
 * Class CronExpressionNotValidException
 * @package EffectConnect\Marketplaces\Exception
 */
class CronExpressionNotValidException extends StateException { }