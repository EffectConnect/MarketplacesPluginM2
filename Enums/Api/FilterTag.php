<?php

namespace EffectConnect\Marketplaces\Enums\Api;

use MyCLabs\Enum\Enum;

/**
 * Class FilterTag
 * @package EffectConnect\Marketplaces\Enums\Api
 * @method static FilterTag ORDER_IMPORT_FAILED_TAG()
 * @method static FilterTag ORDER_IMPORT_SUCCEEDED_TAG()
 * @method static FilterTag ORDER_IMPORT_SKIPPED_TAG()
 * @method static FilterTag EXTERNAL_FULFILMENT_TAG()
 */
class FilterTag extends Enum
{
    /**
     * Order import failed tag.
     */
    const ORDER_IMPORT_FAILED_TAG = 'order_import_failed';

    /**
     * Order import succeeded tag.
     */
    const ORDER_IMPORT_SUCCEEDED_TAG = 'order_import_succeeded';

    /**
     * Order import skipped tag.
     */
    const ORDER_IMPORT_SKIPPED_TAG = 'order_import_skipped';

    /**
     * External fulfilment tag.
     */
    const EXTERNAL_FULFILMENT_TAG = 'external_fulfilment';
}