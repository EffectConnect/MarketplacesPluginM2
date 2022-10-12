<?php

namespace EffectConnect\Marketplaces\Block\Adminhtml\Form\Field;

use EffectConnect\Marketplaces\Model\Config\Source\ShippingMethods;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * HTML select element block with shipment method options
 * @package EffectConnect\Marketplaces\Block\Adminhtml\Form\Field
 */
class ShippingMethod extends Select
{
    /**
     * @var ShippingMethods
     */
    protected $_shippingMethods;

    /**
     * @param Context $context
     * @param ShippingMethods $shippingMethods
     * @param array $data
     */
    public function __construct(
        Context $context,
        ShippingMethods $shippingMethods,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_shippingMethods = $shippingMethods;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->_shippingMethods->toOptionArray());
        }
        return parent::_toHtml();
    }
}
