<?php

namespace EffectConnect\Marketplaces\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package EffectConnect\Marketplaces\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer  = $setup;

        $installer->startSetup();

        $connection = $installer->getConnection();

        if (version_compare($context->getVersion(), "0.0.1", "<")) {
            // Version dependent database mutations.
        }

        $installer->endSetup();
    }
}
