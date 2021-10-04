<?php

namespace EffectConnect\Marketplaces\Exception;

use Magento\Framework\Exception\StateException;

/**
 * Exception thrown when trying to import an order for a country that has a required region ID, but the region ID could not be found.
 *
 * Class OrderImportRegionIdRequiredException
 * @package EffectConnect\Marketplaces\Exception
 */
class OrderImportRegionIdRequiredException extends StateException
{
}