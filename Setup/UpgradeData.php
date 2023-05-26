<?php

namespace EffectConnect\Marketplaces\Setup;

use EffectConnect\Marketplaces\Enums\ExternalFulfilment;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class UpgradeData
 * @package EffectConnect\Marketplaces\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Delivery Time attribute code
     */
    const ATTRIBUTE_DELIVERY_TIME = 'delivery_time';

    /**
     * EAN attribute code
     */
    const ATTRIBUTE_EAN             = 'ean';

    /**
     * @var EavSetupFactory
     */
    protected $_eavSetupFactory;

    /**
     * UpgradeData constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
        $this->createDeliveryTimeAttribute($eavSetup);
        $this->createEanAttribute($eavSetup);

        if (version_compare($context->getVersion(), "1.0.25", "<")) {
            $this->migrateChannelMappingStoreviewId($setup);
        }

        if (version_compare($context->getVersion(), "1.0.28", "<")) {
            $this->copyConnectionStoreviewId($setup);
        }

        if (version_compare($context->getVersion(), "1.0.38", "<")) {
            $this->fixCronConfig();
        }

        $setup->endSetup();
    }

    /**
     * @param EavSetup $eavSetup
     * @throws LocalizedException
     */
    public function createDeliveryTimeAttribute(EavSetup $eavSetup)
    {
        $attribute = $eavSetup->getAttribute(
            Product::ENTITY,
            static::ATTRIBUTE_DELIVERY_TIME
        );

        if (is_array($attribute) && count($attribute) > 0) {
            return;
        }

        $eavSetup->addAttribute(
            Product::ENTITY,
            static::ATTRIBUTE_DELIVERY_TIME,
            [
                'type'                      => 'static',
                'label'                     => __('Delivery time'),
                'input'                     => 'select',
                'source'                    => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'required'                  => false,
                'sort_order'                => 100,
                'position'                  => 100,
                'group'                     => 'EffectConnect',
                'note'                      => __('The delivery time of a product.'),
                'visible'                   => true,
                'system'                    => false,
                'backend'                   => '',
                'frontend'                  => '',
                'class'                     => '',
                'global'                    => ScopedAttributeInterface::SCOPE_WEBSITE,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => true,
                'unique'                    => false,
                'apply_to'                  => ''
            ]
        );
    }

    /**
     * @param EavSetup $eavSetup
     * @throws LocalizedException
     */
    public function createEanAttribute(EavSetup $eavSetup)
    {
        $attribute = $eavSetup->getAttribute(
            Product::ENTITY,
            static::ATTRIBUTE_EAN
        );

        if (is_array($attribute) && count($attribute) > 0) {
            return;
        }

        $eavSetup->addAttribute(
            Product::ENTITY,
            static::ATTRIBUTE_EAN,
            [
                'type'                      => 'static',
                'label'                     => __('EAN'),
                'input'                     => 'text',
                'source'                    => '',
                'required'                  => false,
                'sort_order'                => 100,
                'position'                  => 100,
                'group'                     => 'EffectConnect',
                'note'                      => __('European article number (EAN)'),
                'visible'                   => true,
                'system'                    => false,
                'backend'                   => '',
                'frontend'                  => '',
                'class'                     => '',
                'global'                    => ScopedAttributeInterface::SCOPE_WEBSITE,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => true,
                'unique'                    => false,
                'apply_to'                  => ''
            ]
        );
    }

    /**
     * Migrate storeview_id data to storeview_id_internal and storeview_id_external.
     *
     * @param ModuleDataSetupInterface $setup
     */
    protected function migrateChannelMappingStoreviewId(ModuleDataSetupInterface $setup)
    {
        $tableName = $setup->getTable('ec_marketplaces_channel_mapping');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            // Set storeview_id_internal
            $setup->getConnection()->query('UPDATE ' . $tableName . ' SET `storeview_id_internal` = `storeview_id` WHERE `external_fulfilment` != "' . ExternalFulfilment::EXTERNAL_ORDERS() . '"');

            // Set storeview_id_external
            $setup->getConnection()->query('UPDATE ' . $tableName . ' SET `storeview_id_external` = `storeview_id` WHERE `external_fulfilment` != "' . ExternalFulfilment::INTERNAL_ORDERS() . '"');

            // Remove storeview_id
            $setup->getConnection()->query('UPDATE ' . $tableName . ' SET `storeview_id` = NULL WHERE 1');
        }
    }

    /**
     * Copy image_url_storeview_id to base_url_storeview_id.
     *
     * @param ModuleDataSetupInterface $setup
     */
    protected function copyConnectionStoreviewId(ModuleDataSetupInterface $setup)
    {
        $tableName = $setup->getTable('ec_marketplaces_connection');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            // Set the image url storeview as base storeview.
            $setup->getConnection()->query('UPDATE ' . $tableName . ' SET `base_storeview_id` = `image_url_storeview_id`');
        }
    }

    /**
     * Copy the cron settings from the default group to the effectconnect_marketplaces group and delete the old ones.
     * This is part of the solution for the "No callbacks found" bug.
     */
    protected function fixCronConfig()
    {
        $objectManager  = ObjectManager::getInstance();

        /** @var ScopeConfigInterface $scopeConfig */
        $scopeConfig    = $objectManager->create(ScopeConfigInterface::class);

        /** @var WriterInterface $scopeWriter */
        $scopeWriter    = $objectManager->create(WriterInterface::class);

        $paths = [
            'crontab/%s/jobs/effectconnect_marketplaces_export_catalog/schedule/cron_expr',
            'crontab/%s/jobs/effectconnect_marketplaces_export_catalog/run/model',
            'crontab/%s/jobs/effectconnect_marketplaces_export_offers/schedule/cron_expr',
            'crontab/%s/jobs/effectconnect_marketplaces_export_offers/run/model',
            'crontab/%s/jobs/effectconnect_marketplaces_import_orders/schedule/cron_expr',
            'crontab/%s/jobs/effectconnect_marketplaces_import_orders/run/model',
        ];

        foreach ($paths as $path) {
            $old = sprintf($path, 'default');
            $new = sprintf($path, 'effectconnect_marketplaces');

            $value = $scopeConfig->getValue($old);
            $scopeWriter->save($new, $value);
            $scopeWriter->delete($old);
        }
    }
}
