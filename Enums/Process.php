<?php

namespace EffectConnect\Marketplaces\Enums;

/**
 * Class Process
 * @package EffectConnect\Marketplaces\Enums
 * @method static Process EXPORT_CATALOG()
 * @method static Process EXPORT_OFFERS()
 * @method static Process EXPORT_ORDER_SHIPMENT()
 * @method static Process EXPORT_LOG()
 * @method static Process IMPORT_ORDERS()
 * @method static Process IMPORT_CHANNELS()
 * @method static Process CLEAN_LOG()
 * @method static Process UNDEFINED()
 */
class Process extends AbstractEnum
{
    /**
     * Export Catalog Process.
     */
    const EXPORT_CATALOG        = 'export_catalog';

    /**
     * Export Offers Process.
     */
    const EXPORT_OFFERS         = 'export_offers';

    /**
     * Export Order Shipment Process.
     */
    const EXPORT_ORDER_SHIPMENT = 'export_order_shipment';

    /**
     * Export Log Process.
     */
    const EXPORT_LOG            = 'export_log';

    /**
     * Import Orders Process.
     */
    const IMPORT_ORDERS         = 'import_orders';

    /**
     * Import Channels Process.
     */
    const IMPORT_CHANNELS       = 'import_channels';

    /**
     * Clean Log Process.
     */
    const CLEAN_LOG             = 'clean_log';

    /**
     * Undefined Process.
     */
    const UNDEFINED             = 'undefined';
}