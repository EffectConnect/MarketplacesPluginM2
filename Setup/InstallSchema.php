<?php

namespace EffectConnect\Marketplaces\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package EffectConnect\Marketplaces\Setup
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer  = $setup;

        $installer->startSetup();

        $connection = $installer->getConnection();

        // New database tables.
        $this->addConnectionsTable($installer, $connection);
        $this->addConnectionsStoreviewsTable($installer, $connection);
        $this->addLogsTable($installer, $connection);
        $this->addChannelMappingsTable($installer, $connection);
        $this->addChannelsTable($installer, $connection);
        $this->addOrderLinesTable($installer, $connection);
        $this->addProductOfferExportQueue($installer, $connection);
        $this->addDirectCatalogExportQueue($installer, $connection);
        $this->addLogExportQueue($installer, $connection);

        // Add fields to core Magento database tables.
        $this->extendSalesTable($installer, $connection);

        $installer->endSetup();
    }

    /**
     * Add the table used by the connections model.
     *
     * @param SchemaSetupInterface $installer
     * @param Mysql $connection
     * @throws Zend_Db_Exception
     */
    public function addConnectionsTable(SchemaSetupInterface $installer, Mysql $connection)
    {
        if ($installer->tableExists('ec_marketplaces_connection')) {
            return;
        }

        $tableName = $installer->getTable('ec_marketplaces_connection');

        $table = $connection->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Entity ID'
            )
            ->addColumn(
                'is_active',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'default' => 0
                ],
                'Is active'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Connection name'
            )
            ->addColumn(
                'public_key',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Public key'
            )
            ->addColumn(
                'secret_key',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Secret key'
            )
            ->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => 0
                ],
                'Website ID'
            )
            ->addColumn(
                'image_url_storeview_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => 0
                ],
                'Storeview ID to get image urls from'
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_connection', 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_connection', 'image_url_storeview_id', 'store', 'store_id'),
                'image_url_storeview_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->setComment('EffectConnect Marketplaces - Connections');

        $connection->createTable($table);
    }

    /**
     * Add the table used by the connections storeview to language mapping.
     *
     * @param SchemaSetupInterface $installer
     * @param Mysql $connection
     * @throws Zend_Db_Exception
     */
    public function addConnectionsStoreviewsTable(SchemaSetupInterface $installer, Mysql $connection)
    {
        if ($installer->tableExists('ec_marketplaces_connection_storeview')) {
            return;
        }

        $tableName = $installer->getTable('ec_marketplaces_connection_storeview');

        $table = $connection->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Entity ID'
            )
            ->addColumn(
                'connection_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => 0
                ],
                'Connection ID'
            )
            ->addColumn(
                'storeview_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => 0
                ],
                'Storeview ID'
            )
            ->addColumn(
                'language_code',
                Table::TYPE_TEXT,
                2,
                [
                    'nullable' => false
                ],
                'Language code'
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_connection_storeview', 'connection_id', 'ec_marketplaces_connection', 'entity_id'),
                'connection_id',
                $installer->getTable('ec_marketplaces_connection'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_connection_storeview', 'storeview_id', 'store', 'store_id'),
                'storeview_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->addIndex(
                $installer->getIdxName(
                    'ec_marketplaces_connection_storeview',
                    ['connection_id', 'storeview_id', 'language_code'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['connection_id', 'storeview_id', 'language_code'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment('EffectConnect Marketplaces - Connections Storeview Language Mapping');

        $connection->createTable($table);
    }

    /**
     * Add the table used by the log model.
     *
     * @param SchemaSetupInterface $installer
     * @param Mysql $connection
     * @throws Zend_Db_Exception
     */
    public function addLogsTable(SchemaSetupInterface $installer, Mysql $connection)
    {
        if ($installer->tableExists('ec_marketplaces_log')) {
            return;
        }

        $tableName = $installer->getTable('ec_marketplaces_log');

        $table = $connection->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'unsigned'  => true,
                ],
                'Entity ID'
            )
            ->addColumn(
                'type',
                Table::TYPE_TEXT,
                50,
                [
                    'nullable'  => false
                ],
                'Type (snake-cased) (for example: success, warning, error, etc.)'
            )
            ->addColumn(
                'code',
                Table::TYPE_TEXT,
                100,
                [
                    'nullable'  => false
                ],
                'Code (snake-cased)'
            )
            ->addColumn(
                'process',
                Table::TYPE_TEXT,
                250,
                [
                    'nullable'  => false
                ],
                'Process (snake-cased) (for example: catalog_export, stock_export, order_import, etc.)'
            )
            ->addColumn(
                'connection_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true,
                    'unsigned' => true
                ],
                'Connection ID (foreign key to: ec_marketplaces_connection.entity_id)'
            )
            ->addColumn(
                'subject_type',
                Table::TYPE_TEXT,
                500,
                [
                    'nullable'  => true
                ],
                'Subject Type (the entity type of the log item subject (not mandatory)'
            )
            ->addColumn(
                'subject_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable'  => true,
                    'unsigned'  => true
                ],
                'Subject ID (foreign key to the primary key of the table connected to the model of the type defined in: entity_type) (not mandatory)'
            )
            ->addColumn(
                'message',
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [
                    'nullable'  => false
                ],
                'Message (describes the reason for the log entry)'
            )
            ->addColumn(
                'payload',
                Table::TYPE_TEXT,
                Table::MAX_TEXT_SIZE,
                [
                    'nullable'  => true
                ],
                'Payload (contains the json encoded payload)'
            )
            ->addColumn(
                'occurred_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable'  => false,
                    'default'   => Table::TIMESTAMP_INIT
                ],
                'Occurred At (when the log entry is added)'
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_log', 'connection_id', 'ec_marketplaces_connection', 'entity_id'),
                'connection_id',
                $installer->getTable('ec_marketplaces_connection'),
                'entity_id',
                Table::ACTION_SET_NULL
            )
            ->setComment('EffectConnect Marketplaces - Log');

        $connection->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param Mysql $connection
     * @throws Zend_Db_Exception
     */
    public function addChannelMappingsTable(SchemaSetupInterface $installer, Mysql $connection)
    {
        if ($installer->tableExists('ec_marketplaces_channel_mapping')) {
            return;
        }

        $tableName = $installer->getTable('ec_marketplaces_channel_mapping');

        $table = $connection->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'unsigned'  => true,
                ],
                'Entity ID'
            )
            ->addColumn(
                'connection_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable'  => false,
                    'unsigned'  => true
                ],
                'Connection ID (foreign key to: ec_marketplaces_connection.entity_id)'
            )
            ->addColumn(
                'channel_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true,
                    'unsigned' => true,
                    'default' => 0
                ],
                'Channel ID (foreign key to: ec_marketplaces_channel.entity_id)'
            )
            ->addColumn(
                'storeview_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => true,
                    'unsigned' => true,
                    'default' => 0
                ],
                'Storeview ID (foreign key to: store.store_id)'
            )
            ->addColumn(
                'customer_create',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                    'default'  => 2
                ],
                'Create customer'
            )
            ->addColumn(
                'customer_group_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true,
                    'default'  => null
                ],
                'Customer group ID (foreign key to: customer_group.customer_group_id)'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true,
                    'default'  => null
                ],
                'Customer ID (foreign key to: customer_entity.entity_id)'
            )
            ->addColumn(
                'send_emails',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                    'default'  => 2
                ],
                'Send emails'
            )
            ->addColumn(
                'payment_method',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true,
                    'default'  => null
                ],
                'Payment method'
            )
            ->addColumn(
                'shipping_method',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true,
                    'default'  => null
                ],
                'Shipping method'
            )
            ->addColumn(
                'external_fulfilment',
                Table::TYPE_TEXT,
                50,
                [
                    'nullable' => false
                ],
                'External fulfilment (snake-cased) (for example: internal_orders, external_orders, etc.)'
            )
            ->addColumn(
                'discount_code',
                Table::TYPE_TEXT,
                64,
                [
                    'nullable' => false
                ],
                'Discount code'
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_channel_mapping', 'connection_id', 'ec_marketplaces_connection', 'entity_id'),
                'connection_id',
                $installer->getTable('ec_marketplaces_connection'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_channel_mapping', 'channel_id', 'ec_marketplaces_channel', 'entity_id'),
                'channel_id',
                $installer->getTable('ec_marketplaces_channel'),
                'entity_id',
                Table::ACTION_SET_NULL
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_channel_mapping', 'storeview_id', 'store', 'store_id'),
                'storeview_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_SET_NULL
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_channel_mapping', 'customer_group_id', 'customer_group', 'customer_group_id'),
                'customer_group_id',
                $installer->getTable('customer_group'),
                'customer_group_id',
                Table::ACTION_SET_NULL
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_channel_mapping', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_SET_NULL
            )
            ->addIndex(
                $installer->getIdxName(
                    'ec_marketplaces_channel_mapping',
                    ['connection_id', 'channel_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['connection_id', 'channel_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment('EffectConnect Marketplaces - Channnel Mapping');

        $connection->createTable($table);
    }

    /**
     * Add 'ec_marketplaces_identification_number' and 'ec_marketplaces_channel_number' field to sales table (and grid table).
     *
     * @param SchemaSetupInterface $installer
     * @param Mysql $connection
     * @throws Zend_Db_Exception
     */
    public function extendSalesTable(SchemaSetupInterface $installer, Mysql $connection)
    {
        if (!$installer->tableExists('sales_order') || !$installer->tableExists('sales_order_grid')) {
            return;
        }

        // https://magento.stackexchange.com/questions/48118/addattribute-vs-addcolumn
        // addColumn adds a column to a table.
        // addAttribute adds a new attribute for EAV entities. So a new record will be created in the eav_attribute table.
        // The exception: For the sales module, addColumn and addAttribute do the same thing.
        // They add a column to a table. The reason is backwards compatibility.
        // Before version 1.4, the sales entities (orders, invoices, shipments, items, ...) were EAV so you had to use addAttribute.
        // Starting with 1.4 the sales entities are flat so you have to use addColumn.
        // But for backwards compatibility, starting version 1.4, addAttribute is just a wrapper for addColumn.

        // Add EffectConnect order number (used for identification)
        $connection->addColumn(
            $installer->getTable('sales_order'),
            'ec_marketplaces_identification_number',
            [
                'type'    => Table::TYPE_TEXT,
                'length'  => 64,
                'comment' => 'EffectConnect Marketplaces Order Number (used for identification)'
            ]
        );
        $connection->addColumn(
            $installer->getTable('sales_order_grid'),
            'ec_marketplaces_identification_number',
            [
                'type'    => Table::TYPE_TEXT,
                'length'  => 64,
                'comment' => 'EffectConnect Marketplaces Order Number (used for identification)'
            ]
        );

        // Add channel number (info field)
        $connection->addColumn(
            $installer->getTable('sales_order'),
            'ec_marketplaces_channel_number',
            [
                'type'    => Table::TYPE_TEXT,
                'length'  => 64,
                'comment' => 'EffectConnect Marketplaces Channel Number'
            ]
        );
        $connection->addColumn(
            $installer->getTable('sales_order_grid'),
            'ec_marketplaces_channel_number',
            [
                'type'    => Table::TYPE_TEXT,
                'length'  => 64,
                'comment' => 'EffectConnect Marketplaces Channel Number'
            ]
        );

        // Add connection id
        $connection->addColumn(
            $installer->getTable('sales_order'),
            'ec_marketplaces_connection_id',
            [
                'type'    => Table::TYPE_INTEGER,
                'comment' => 'EffectConnect Marketplaces Connection ID',
                'unsigned' => true,
                'nullable' => true,
                'default'  => null
            ]
        );
        $connection->addForeignKey(
            $installer->getFkName('sales_order', 'ec_marketplaces_connection_id', 'ec_marketplaces_connection', 'entity_id'),
            $installer->getTable('sales_order'),
            'ec_marketplaces_connection_id',
            $installer->getTable('ec_marketplaces_connection'),
            'entity_id',
            $onDelete = AdapterInterface::FK_ACTION_SET_NULL
        );
        $connection->addColumn(
            $installer->getTable('sales_order_grid'),
            'ec_marketplaces_connection_id',
            [
                'type'    => Table::TYPE_INTEGER,
                'comment' => 'EffectConnect Marketplaces Connection ID',
                'unsigned' => true,
                'nullable' => true,
                'default'  => null
            ]
        );
        $connection->addForeignKey(
            $installer->getFkName('sales_order_grid', 'ec_marketplaces_connection_id', 'ec_marketplaces_connection', 'entity_id'),
            $installer->getTable('sales_order_grid'),
            'ec_marketplaces_connection_id',
            $installer->getTable('ec_marketplaces_connection'),
            'entity_id',
            $onDelete = AdapterInterface::FK_ACTION_SET_NULL
        );
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param Mysql $connection
     * @throws Zend_Db_Exception
     */
    public function addChannelsTable(SchemaSetupInterface $installer, Mysql $connection)
    {
        if ($installer->tableExists('ec_marketplaces_channel')) {
            return;
        }

        $tableName = $installer->getTable('ec_marketplaces_channel');

        $table = $connection->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'unsigned'  => true,
                ],
                'Entity ID'
            )
            ->addColumn(
                'connection_id',
                Table::TYPE_INTEGER,
                11,
                [
                    'nullable'  => true,
                    'unsigned'  => true
                ],
                'Connection ID (foreign key to: ec_marketplaces_connection.entity_id)'
            )
            ->addColumn(
                'ec_channel_id',
                Table::TYPE_INTEGER,
                11,
                [
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 0
                ],
                'EffectConnect Channel ID'
            )
            ->addColumn(
                'ec_channel_type',
                Table::TYPE_TEXT,
                16,
                [
                    'nullable' => false,
                ],
                'EffectConnect Channel Type'
            )
            ->addColumn(
                'ec_channel_subtype',
                Table::TYPE_TEXT,
                16,
                [
                    'nullable' => false,
                ],
                'EffectConnect Channel Subtype'
            )
            ->addColumn(
                'ec_channel_title',
                Table::TYPE_TEXT,
                64,
                [
                    'nullable' => false,
                ],
                'EffectConnect Channel Title'
            )
            ->addColumn(
                'ec_channel_language',
                Table::TYPE_TEXT,
                2,
                [
                    'nullable' => false,
                ],
                'EffectConnect Channel Language'
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_channel', 'connection_id', 'ec_marketplaces_connection', 'entity_id'),
                'connection_id',
                $installer->getTable('ec_marketplaces_connection'),
                'entity_id',
                Table::ACTION_SET_NULL
            )
            ->addIndex(
                $installer->getIdxName(
                    'ec_marketplaces_channel',
                    ['connection_id', 'ec_channel_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['connection_id', 'ec_channel_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment('EffectConnect Marketplaces - Channnels');

        $connection->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param Mysql $connection
     * @throws Zend_Db_Exception
     */
    public function addOrderLinesTable(SchemaSetupInterface $installer, Mysql $connection)
    {
        if ($installer->tableExists('ec_marketplaces_order_lines')) {
            return;
        }

        $tableName = $installer->getTable('ec_marketplaces_order_lines');

        $table = $connection->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'unsigned'  => true,
                ],
                'Entity ID'
            )
            ->addColumn(
                'ec_order_line_id',
                Table::TYPE_TEXT,
                64,
                [
                    'nullable' => false,
                ],
                'EffectConnect Order Line ID'
            )
            ->addColumn(
                'quote_item_id',
                Table::TYPE_INTEGER,
                11,
                [
                    'nullable'  => false,
                    'unsigned'  => true
                ],
                'Quote Item ID (foreign key to: quote_item.item_id)'
            )
            ->addColumn(
                'shipment_id',
                Table::TYPE_INTEGER,
                11,
                [
                    'nullable'  => true,
                    'unsigned'  => true
                ],
                'Shipment ID (foreign key to: sales_shipment.entity_id)'
            )
            ->addColumn(
                'track_id',
                Table::TYPE_INTEGER,
                11,
                [
                    'nullable'  => true,
                    'unsigned'  => true
                ],
                'Shipment Track ID (foreign key to: sales_shipment_track.entity_id)'
            )
            ->addColumn(
                'track_exported_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable'  => true
                ],
                'Time the tracking code was exported to EffectConnect'
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_order_lines', 'quote_item_id', 'quote_item', 'item_id'),
                'quote_item_id',
                $installer->getTable('quote_item'),
                'item_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_order_lines', 'shipment_id', 'sales_shipment', 'entity_id'),
                'shipment_id',
                $installer->getTable('sales_shipment'),
                'entity_id',
                Table::ACTION_SET_NULL
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_order_lines', 'track_id', 'sales_shipment_track', 'entity_id'),
                'track_id',
                $installer->getTable('sales_shipment_track'),
                'entity_id',
                Table::ACTION_SET_NULL
            )
            ->addIndex(
                $installer->getIdxName(
                    'ec_marketplaces_order_lines',
                    ['quote_item_id', 'ec_order_line_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['quote_item_id', 'ec_order_line_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment('EffectConnect Marketplaces - Table for matching EC order lines to Magento quote items');

        $connection->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param Mysql $connection
     * @throws Zend_Db_Exception
     */
    public function addProductOfferExportQueue(SchemaSetupInterface $installer, Mysql $connection)
    {
        if ($installer->tableExists('ec_marketplaces_product_offer_export_queue')) {
            return;
        }

        $tableName = $installer->getTable('ec_marketplaces_product_offer_export_queue');

        $table = $connection->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'unsigned'  => true
                ],
                'Entity ID'
            )
            ->addColumn(
                'catalog_product_entity_id',
                Table::TYPE_INTEGER,
                10,
                [
                    'unsigned'  => true,
                    'nullable'  => false
                ],
                'Magento Catalog Product Entity ID'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable'  => false,
                    'default'   => Table::TIMESTAMP_INIT
                ],
                'Created At'
            )
            ->addColumn(
                'executed_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable'  => true
                ],
                'Executed At'
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_product_offer_export_queue', 'catalog_product_entity_id', 'catalog_product_entity', 'entity_id'),
                'catalog_product_entity_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('EffectConnect Marketplaces - Table for queueing product offer exports for specific products.');

        $connection->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param Mysql $connection
     * @throws Zend_Db_Exception
     */
    public function addDirectCatalogExportQueue(SchemaSetupInterface $installer, Mysql $connection)
    {
        if ($installer->tableExists('ec_marketplaces_direct_catalog_export_queue')) {
            return;
        }

        $tableName = $installer->getTable('ec_marketplaces_direct_catalog_export_queue');

        $table = $connection->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'unsigned'  => true
                ],
                'Entity ID'
            )
            ->addColumn(
                'connection_entity_id',
                Table::TYPE_INTEGER,
                10,
                [
                    'unsigned'  => true,
                    'nullable'  => false
                ],
                'EffectConnect Marketplaces Connection Entity ID'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable'  => false,
                    'default'   => Table::TIMESTAMP_INIT
                ],
                'Created At'
            )
            ->addColumn(
                'executed_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable'  => true
                ],
                'Executed At'
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_direct_catalog_export_queue', 'connection_entity_id', 'ec_marketplaces_connection', 'entity_id'),
                'connection_entity_id',
                $installer->getTable('ec_marketplaces_connection'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('EffectConnect Marketplaces - Table for queueing direct catalog exports for specific connections.');

        $connection->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param Mysql $connection
     * @throws Zend_Db_Exception
     */
    public function addLogExportQueue(SchemaSetupInterface $installer, Mysql $connection)
    {
        if ($installer->tableExists('ec_marketplaces_log_export_queue')) {
            return;
        }

        $tableName = $installer->getTable('ec_marketplaces_log_export_queue');

        $table = $connection->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'unsigned'  => true
                ],
                'Entity ID'
            )
            ->addColumn(
                'connection_entity_id',
                Table::TYPE_INTEGER,
                10,
                [
                    'unsigned'  => true,
                    'nullable'  => false
                ],
                'EffectConnect Marketplaces Connection Entity ID'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable'  => false,
                    'default'   => Table::TIMESTAMP_INIT
                ],
                'Created At'
            )
            ->addColumn(
                'executed_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable'  => true
                ],
                'Executed At'
            )
            ->addForeignKey(
                $installer->getFkName('ec_marketplaces_log_export_queue', 'connection_entity_id', 'ec_marketplaces_connection', 'entity_id'),
                'connection_entity_id',
                $installer->getTable('ec_marketplaces_connection'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('EffectConnect Marketplaces - Table for queueing log exports for specific connections.');

        $connection->createTable($table);
    }
}
