<?php

namespace EffectConnect\Marketplaces\Interfaces;

/**
 * Contains all settings for the EffectConnect Marketplaces module.
 *
 * Interface SettingPathsInterface
 * @package EffectConnect\Marketplaces\Interfaces
 */
interface SettingPathsInterface
{
    /** Get the catalog export schedule, used when exporting the catalog. */
    const SETTING_CATALOG_EXPORT_SCHEDULE                   = 'catalog_export_settings/schedule';

    /** Get the custom catalog export schedule, used when exporting the catalog and the schedule is set to custom. */
    const SETTING_CATALOG_EXPORT_CUSTOM_SCHEDULE            = 'catalog_export_settings/custom_schedule';

    /** Get the number of products per page when obtaining the catalog for the catalog export. */
    const SETTING_CATALOG_EXPORT_PAGE_SIZE                  = 'catalog_export_settings/page_size';

    /** Get the brand attribute for products, used when exporting the catalog. */
    const SETTING_CATALOG_EXPORT_BRAND_ATTRIBUTE            = 'catalog_export_settings/attribute_brand';

    /** Get the title attribute for products, used when exporting the catalog. */
    const SETTING_CATALOG_EXPORT_TITLE_ATTRIBUTE            = 'catalog_export_settings/attribute_title';

    /** Get the description attribute for products, used when exporting the catalog. */
    const SETTING_CATALOG_EXPORT_DESCRIPTION_ATTRIBUTE      = 'catalog_export_settings/attribute_description';

    /** Get the EAN attribute for products, used when exporting the catalog. */
    const SETTING_CATALOG_EXPORT_EAN_ATTRIBUTE              = 'catalog_export_settings/attribute_ean';

    /** Get the cost attribute for products, used when exporting the catalog. */
    const SETTING_CATALOG_EXPORT_COST_ATTRIBUTE             = 'catalog_export_settings/attribute_cost';

    /** Get the delivery time attribute for products, used when exporting the catalog. */
    const SETTING_CATALOG_EXPORT_DELIVERY_TIME_ATTRIBUTE    = 'catalog_export_settings/attribute_delivery_time';

    /** Get the price attribute for products, used when exporting the catalog. */
    const SETTING_CATALOG_EXPORT_PRICE_ATTRIBUTE            = 'catalog_export_settings/attribute_price';

    /** Get whether the default price attribute should be used when the custom price attribute is empty. */
    const SETTING_CATALOG_EXPORT_PRICE_FALLBACK             = 'catalog_export_settings/price_fallback';

    /** Get whether the special price attribute should be used when exporting the catalog. */
    const SETTING_CATALOG_EXPORT_USE_SPECIAL_PRICE          = 'catalog_export_settings/use_special_price';


    /** Get the catalog export schedule, used when exporting the catalog. */
    const SETTING_OFFER_EXPORT_SCHEDULE                     = 'offer_export_settings/schedule';

    /** Get the custom catalog export schedule, used when exporting the catalog and the schedule is set to custom. */
    const SETTING_OFFER_EXPORT_CUSTOM_SCHEDULE              = 'offer_export_settings/custom_schedule';

    /** Get the queue size of the offer export queue handler. */
    const SETTING_OFFER_EXPORT_QUEUE_SIZE                   = 'offer_export_settings/queue_size';

    /** Get the number of products per page when obtaining the catalog for the offer export. */
    const SETTING_OFFER_EXPORT_PAGE_SIZE                    = 'offer_export_settings/page_size';

    /** Determines whether Magento MSI is available, enabled and active in the current Magento version and configuration (value is computed). */
    const SETTING_OFFER_EXPORT_MSI_ACTIVE                   = 'offer_export_settings/msi_active';

    /** Determines whether the physical stock quantity, the salable stock quantity or the traditional (non-MSI) quantity should be used. */
    const SETTING_OFFER_EXPORT_QUANTITY_TYPE                = 'offer_export_settings/quantity_type';

    /** Whether to base a product's salable stock quantity on a website or on a stock. */
    const SETTING_OFFER_EXPORT_SALABLE_SOURCE               = 'offer_export_settings/salable_source';

    /** The website to base the salable stock quantity for a product on. */
    const SETTING_OFFER_EXPORT_WEBSITE                      = 'offer_export_settings/website';

    /** The stock to base the salable stock quantity for a product on. */
    const SETTING_OFFER_EXPORT_STOCK                        = 'offer_export_settings/stock';

    /** The Magento MSI source whether the quantity should be used by predefined sources, sources in predefined stocks or sources defined on product level.. */
    const SETTING_OFFER_EXPORT_MSI_TYPE                     = 'offer_export_settings/msi_type';

    /** The sources to base the stock quantity on for a product. */
    const SETTING_OFFER_EXPORT_SOURCES                      = 'offer_export_settings/sources';

    /** The stocks to base the stock quantity on for a product. */
    const SETTING_OFFER_EXPORT_STOCKS                       = 'offer_export_settings/stocks';

    /** When disabled stock will be fictional based on the fictional stock setting . */
    const SETTING_OFFER_EXPORT_STOCK_TRACKING               = 'offer_export_settings/stock_tracking';

    /** The fictional stock used for all products and product options when stock tracking is disabled. */
    const SETTING_OFFER_EXPORT_FICTIONAL_STOCK              = 'offer_export_settings/fictional_stock';


    /** Get the order import schedule, used when importing orders. */
    const SETTING_ORDER_IMPORT_SCHEDULE                     = 'order_import_settings/schedule';

    /** Get the custom order import schedule, used when importing orders and the schedule is set to custom. */
    const SETTING_ORDER_IMPORT_CUSTOM_SCHEDULE              = 'order_import_settings/custom_schedule';

    /** Default payment method for imported orders. */
    const SETTING_ORDER_IMPORT_PAYMENT_METHOD               = 'order_import_settings/payment_method';

    /** Default shipping method for imported orders. */
    const SETTING_ORDER_IMPORT_SHIPPING_METHOD              = 'order_import_settings/shipping_method';

    /** Default shipping method for imported orders. */
    const SETTING_ORDER_IMPORT_ORDER_STATUS                 = 'order_import_settings/order_status';

    /** Create customer for imported orders. */
    const SETTING_ORDER_IMPORT_CUSTOMER_CREATE              = 'order_import_settings/customer_create';

    /** Group to assign the created customer to. */
    const SETTING_ORDER_IMPORT_CUSTOMER_GROUP_ID            = 'order_import_settings/customer_group_id';

    /** Whether or not an invoice needs to be created when importing an order. */
    const SETTING_ORDER_IMPORT_CREATE_INVOICE               = 'order_import_settings/create_invoice';

    /** Whether or not to send an email to the customer for each imported order. */
    const SETTING_ORDER_IMPORT_SEND_EMAILS                  = 'order_import_settings/send_emails';


    /** Get the queue size of the order shipment export queue handler. */
    const SETTING_SHIPMENT_EXPORT_QUEUE_SIZE                = 'shipment_export_settings/queue_size';


    /** Get the expiration for log entries. */
    const SETTING_LOG_EXPIRATION                            = 'log_settings/log_expiration';

    /** Get the number of log items per page when obtaining the log items for the log export. */
    const SETTING_LOG_PAGE_SIZE                             = 'log_settings/page_size';


    /** Get the number of seconds before an API call should time out. */
    const SETTING_ADVANCED_API_CALL_TIMEOUT                  = 'advanced_settings/api_call_timeout';
}