<?php

namespace EffectConnect\Marketplaces\Enums;

/**
 * Class LogCode
 * @package EffectConnect\Marketplaces\Enums
 * @method static LogCode CATALOG_EXPORT_HAS_STARTED()
 * @method static LogCode CATALOG_EXPORT_HAS_ENDED()
 * @method static LogCode CATALOG_EXPORT_CONNECTION_FAILED()
 * @method static LogCode CATALOG_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET()
 * @method static LogCode CATALOG_EXPORT_EAN_NOT_VALID()
 * @method static LogCode CATALOG_EXPORT_EAN_ALREADY_IN_USE()
 * @method static LogCode CATALOG_EXPORT_PRODUCT_OPTION_ALREADY_IN_EXPORT()
 * @method static LogCode CATALOG_EXPORT_PRODUCT_HAS_NO_VALID_OPTIONS()
 * @method static LogCode CATALOG_EXPORT_PRODUCT_NOT_FOUND()
 * @method static LogCode CATALOG_EXPORT_FILE_CREATION_FAILED()
 * @method static LogCode CATALOG_EXPORT_XML_GENERATION_FAILED()
 * @method static LogCode CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MAXIMUM()
 * @method static LogCode CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MINIMUM()
 * @method static LogCode CATALOG_EXPORT_PRODUCT_NOT_ENABLED()
 * @method static LogCode CATALOG_EXPORT_PRODUCT_NOT_VISIBLE()
 * @method static LogCode CATALOG_EXPORT_PRODUCT_TYPE_NOT_SUPPORTED()
 * @method static LogCode CATALOG_EXPORT_BUNDLE_EXPORT_DISABLED()
 * @method static LogCode CATALOG_EXPORT_UNSUPPORTED_BUNDLE()
 * @method static LogCode CATALOG_EXPORT_MAXIMUM_IMAGES_EXCEEDED()
 * @method static LogCode CATALOG_EXPORT_NO_STOREVIEW_MAPPING_DEFINED()
 * @method static LogCode CATALOG_EXPORT_PRODUCT_HAS_NO_SKU()
 * @method static LogCode OFFERS_EXPORT_HAS_STARTED()
 * @method static LogCode OFFERS_EXPORT_HAS_ENDED()
 * @method static LogCode OFFERS_EXPORT_PRODUCT_SUCCESS()
 * @method static LogCode OFFERS_EXPORT_PRODUCT_FAILED()
 * @method static LogCode OFFERS_EXPORT_CONNECTION_FAILED()
 * @method static LogCode OFFERS_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET()
 * @method static LogCode OFFERS_EXPORT_PRODUCT_NOT_FOUND()
 * @method static LogCode OFFERS_EXPORT_FILE_CREATION_FAILED()
 * @method static LogCode OFFERS_EXPORT_XML_GENERATION_FAILED()
 * @method static LogCode LOG_LOG_CLEANED()
 * @method static LogCode LOG_EXPORT_SAVE_ERROR()
 * @method static LogCode LOG_EXPORT_CONNECTION_ERROR()
 * @method static LogCode LOG_EXPORT_ALLOWED_CALL_ERROR()
 * @method static LogCode LOG_EXPORT_CREATE_ERROR()
 * @method static LogCode LOG_EXPORT_FILE_CREATION_FAILED()
 * @method static LogCode LOG_EXPORT_XML_GENERATION_FAILED()
 * @method static LogCode LOG_EXPORT_SUCCEEDED()
 * @method static LogCode ORDER_IMPORT_FAILED()
 * @method static LogCode ORDER_IMPORT_SUCCEEDED()
 * @method static LogCode ORDER_IMPORT_SKIPPED()
 * @method static LogCode ORDER_IMPORT_DISCOUNT_CODE_FAILED()
 * @method static LogCode ORDER_IMPORT_SEND_ORDER_EMAIL_FAILED()
 * @method static LogCode ORDER_IMPORT_ALREADY_EXISTS()
 * @method static LogCode ORDER_IMPORT_HAS_STARTED()
 * @method static LogCode ORDER_IMPORT_HAS_ENDED()
 * @method static LogCode ORDER_IMPORT_UPDATE_FAILED()
 * @method static LogCode ORDER_IMPORT_ADD_TAG_FAILED()
 * @method static LogCode ORDER_IMPORT_NO_ORDERS_AVAILABLE()
 * @method static LogCode CHANNEL_IMPORT_EXECUTED()
 * @method static LogCode SHIPMENT_EXPORT_SUCCEEDED()
 * @method static LogCode SHIPMENT_EXPORT_FAILED()
 * @method static LogCode SHIPMENT_EXPORT_SKIPPED()
 */
class LogCode extends AbstractEnum
{
    /**
     * Exporting the catalog has started.
     */
    const CATALOG_EXPORT_HAS_STARTED                            = 'catalog_export_has_started';

    /**
     * Exporting the catalog has ended.
     */
    const CATALOG_EXPORT_HAS_ENDED                              = 'catalog_export_has_ended';

    /**
     * Exporting the catalog for a certain connection has failed.
     */
    const CATALOG_EXPORT_CONNECTION_FAILED                      = 'catalog_export_connection_failed';

    /**
     * An obligated attribute is not set for a certain product (option).
     */
    const CATALOG_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET            = 'catalog_export_obligated_attribute_not_set';

    /**
     * EAN from a certain product (option) is not valid.
     */
    const CATALOG_EXPORT_EAN_NOT_VALID                          = 'catalog_export_ean_not_valid';

    /**
     * EAN for current product is already used by another product.
     */
    const CATALOG_EXPORT_EAN_ALREADY_IN_USE                     = 'catalog_export_ean_already_in_use';

    /**
     * Option ID is already added to the export (this can be the case when a simple product is linked to multiple configurable products).
     */
    const CATALOG_EXPORT_PRODUCT_OPTION_ALREADY_IN_EXPORT       = 'catalog_export_product_option_already_in_export';

    /**
     * A certain product has no (valid) options.
     */
    const CATALOG_EXPORT_PRODUCT_HAS_NO_VALID_OPTIONS           = 'catalog_export_product_has_no_valid_options';

    /**
     * A certain product was not found.
     */
    const CATALOG_EXPORT_PRODUCT_NOT_FOUND                      = 'catalog_export_product_not_found';

    /**
     * Creating a file failed.
     */
    const CATALOG_EXPORT_FILE_CREATION_FAILED                   = 'catalog_export_file_creation_failed';

    /**
     * Generating XML failed.
     */
    const CATALOG_EXPORT_XML_GENERATION_FAILED                  = 'catalog_export_xml_generation_failed';

    /**
     * Attribute value reached maximum allowed value.
     */
    const CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MAXIMUM        = 'catalog_export_attribute_value_reached_maximum';

    /**
     * Attribute value reached minimum allowed value.
     */
    const CATALOG_EXPORT_ATTRIBUTE_VALUE_REACHED_MINIMUM        = 'catalog_export_attribute_value_reached_minimum';

    /**
     * The product is not enabled and is therefor not exported.
     */
    const CATALOG_EXPORT_PRODUCT_NOT_ENABLED                    = 'catalog_export_product_not_enabled';

    /**
     * The product is not visible and is therefor not exported.
     */
    const CATALOG_EXPORT_PRODUCT_NOT_VISIBLE                    = 'catalog_export_product_not_visible';

    /**
     * The product type is not supported, the product will not be exported.
     */
    const CATALOG_EXPORT_PRODUCT_TYPE_NOT_SUPPORTED             = 'catalog_export_product_type_not_supported';

    /**
     * The bundle export is disabled, the product will not be exported.
     */
    const CATALOG_EXPORT_BUNDLE_EXPORT_DISABLED                 = 'catalog_export_bundle_export_disabled';

    /**
     * Currently only bundle products with exactly one option for each bundle item are supported, otherwise the product will not be exported.
     */
    const CATALOG_EXPORT_UNSUPPORTED_BUNDLE                     = 'catalog_export_unsupported_bundle';

    /**
     * Product exceeds maximum amount of images.
     */
    const CATALOG_EXPORT_MAXIMUM_IMAGES_EXCEEDED                = 'catalog_export_maximum_images_exceeded';

    /**
     * There are no storeview mappings in the current connection.
     */
    const CATALOG_EXPORT_NO_STOREVIEW_MAPPING_DEFINED           = 'catalog_export_no_storeview_mapping_defined';

    /**
     * A certain product (option) has no SKU.
     */
    const CATALOG_EXPORT_PRODUCT_HAS_NO_SKU                     = 'catalog_export_product_has_no_sku';

    /**
     * Exporting the offers has started.
     */
    const OFFERS_EXPORT_HAS_STARTED                             = 'offers_export_has_started';

    /**
     * Exporting the offers has ended.
     */
    const OFFERS_EXPORT_HAS_ENDED                               = 'offers_export_has_ended';

    /**
     * Exporting the offer for one product has succeeded.
     */
    const OFFERS_EXPORT_PRODUCT_SUCCESS                         = 'offers_export_product_success';

    /**
     * Exporting the offer for one product has failed.
     */
    const OFFERS_EXPORT_PRODUCT_FAILED                          = 'offers_export_product_failed';

    /**
     * Exporting the offers for a certain connection has failed.
     */
    const OFFERS_EXPORT_CONNECTION_FAILED                       = 'offers_export_connection_failed';

    /**
     * An obligated attribute is not set for a certain product (option).
     */
    const OFFERS_EXPORT_OBLIGATED_ATTRIBUTE_NOT_SET             = 'offers_export_obligated_attribute_not_set';

    /**
     * A certain product was not found.
     */
    const OFFERS_EXPORT_PRODUCT_NOT_FOUND                       = 'offers_export_product_not_found';

    /**
     * Creating a file failed.
     */
    const OFFERS_EXPORT_FILE_CREATION_FAILED                    = 'offers_export_file_creation_failed';

    /**
     * Generating XML failed.
     */
    const OFFERS_EXPORT_XML_GENERATION_FAILED                   = 'offers_export_xml_generation_failed';

    /**
     * The log clean function has been executed.
     */
    const LOG_LOG_CLEANED                                       = 'log_log_cleaned';

    /**
     * The log export failed when saving the queue item.
     */
    const LOG_EXPORT_SAVE_ERROR                                 = 'log_export_save_error';

    /**
     * The log export failed because the connection could not be established.
     */
    const LOG_EXPORT_CONNECTION_ERROR                           = 'log_export_connection_error';

    /**
     * The API call to EffectConnect Marketplaces to check if exporting log is allowed failed.
     */
    const LOG_EXPORT_ALLOWED_CALL_ERROR                         = 'log_export_allowed_call_error';

    /**
     * The log export failed when trying to send the log to EffectConnect Marketplaces.
     */
    const LOG_EXPORT_CREATE_ERROR                               = 'log_export_create_error';

    /**
     * Creating a file failed.
     */
    const LOG_EXPORT_FILE_CREATION_FAILED                       = 'log_export_file_creation_failed';

    /**
     * Generating XML failed.
     */
    const LOG_EXPORT_XML_GENERATION_FAILED                      = 'log_export_xml_generation_failed';

    /**
     * Log export succeeded.
     */
    const LOG_EXPORT_SUCCEEDED                                  = 'log_export_succeeded';

    /**
     * The order import failed for current order.
     */
    const ORDER_IMPORT_FAILED                                   = 'order_import_failed';

    /**
     * The order import succeeded for current order.
     */
    const ORDER_IMPORT_SUCCEEDED                                = 'order_import_succeeded';

    /**
     * The order import was skipped for current order.
     */
    const ORDER_IMPORT_SKIPPED                                  = 'order_import_skipped';

    /**
     * The order import tried to apply a discount code, but it was invalid (not critical).
     */
    const ORDER_IMPORT_DISCOUNT_CODE_FAILED                     = 'order_import_discount_code_failed';

    /**
     * The order import failed when sending email to customer (not critical).
     */
    const ORDER_IMPORT_SEND_ORDER_EMAIL_FAILED                  = 'order_import_send_order_email_failed';

    /**
     * The order import was skipped for current order because order already exists.
     */
    const ORDER_IMPORT_ALREADY_EXISTS                           = 'order_import_already_exists';

    /**
     * The order import was started.
     */
    const ORDER_IMPORT_HAS_STARTED                              = 'order_import_has_started';

    /**
     * The order import was ended.
     */
    const ORDER_IMPORT_HAS_ENDED                                = 'order_import_has_ended';

    /**
     * The order update to EffectConnect after importing an order failed.
     */
    const ORDER_IMPORT_UPDATE_FAILED                            = 'order_import_update_failed';

    /**
     * Adding order tag to EffectConnect failed after importing an order.
     */
    const ORDER_IMPORT_ADD_TAG_FAILED                           = 'order_import_add_tag_failed';

    /**
     * There are no orders available in EC that should be imported.
     */
    const ORDER_IMPORT_NO_ORDERS_AVAILABLE                      = 'order_import_no_orders_available';

    /**
     * The channel import executed.
     */
    const CHANNEL_IMPORT_EXECUTED                               = 'channel_import_executed';

    /**
     * The shipment export to EffectConnect has succeeded.
     */

    const SHIPMENT_EXPORT_SUCCEEDED                             = 'shipment_export_succeeded';

    /**
     * The shipment export to EffectConnect has failed.
     */
    const SHIPMENT_EXPORT_FAILED                                = 'shipment_export_failed';

    /**
     * The shipment export to EffectConnect has been skipped.
     */
    const SHIPMENT_EXPORT_SKIPPED                               = 'shipment_export_skipped';
}