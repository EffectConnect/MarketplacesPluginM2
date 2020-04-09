<?php

namespace EffectConnect\Marketplaces\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Zend_Validate_Exception;

/**
 * Class InstallData
 * @package EffectConnect\Marketplaces\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * Delivery Time attribute code
     */
    const ATTRIBUTE_DELIVERY_TIME   = 'delivery_time';

    /**
     * EAN attribute code
     */
    const ATTRIBUTE_EAN             = 'ean';

    /**
     * @var EavSetupFactory
     */
    protected $_eavSetupFactory;

    /**
     * InstallData constructor
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
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
        $this->createDeliveryTimeAttribute($eavSetup);
        $this->createEanAttribute($eavSetup);
    }

    /**
     * @param EavSetup $eavSetup
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
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
     * @throws Zend_Validate_Exception
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
                'type'                      => 'varchar',
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
}
