<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Laminas\Filter\Word\UnderscoreToSeparator;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\DataObject;

/**
 * Class Attribute
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class Attribute implements OptionSourceInterface
{
    /**
     * @var Collection
     */
    protected $_attributeCollection;

    /**
     * Attribute constructor.
     * @param Collection $attributeCollection
     */
    public function __construct(Collection $attributeCollection)
    {
        $this->_attributeCollection = $attributeCollection;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @param bool $optional
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(bool $optional = false)
    {
        $options = array_map(function(DataObject $attribute) {
            $code   = $attribute->getAttributeCode();
            $label  = $attribute->getFrontendLabel();

            /* Needed when no label is defined */
            $codeLabel = ucwords((new UnderscoreToSeparator())->filter($code));

            return [
                'value' => $code,
                'label' => (!empty($label) ? $label : $codeLabel) . ' (' . $code . ')',
                'type'  => [
                    'field' => $attribute->getFrontendInput(),
                    'data'  => $attribute->getBackendType()
                ]
            ];
        }, $this->_attributeCollection->getItems());

        usort($options, function($a, $b) {
            return $a['label'] <=> $b['label'];
        });

        if ($optional) {
            array_unshift($options, [
                'value' => '',
                'label' => __('None'),
                'type'  => [
                    'field' => 'none',
                    'data'  => 'none'
                ]
            ]);
        }

        return $options;
    }
}