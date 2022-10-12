<?php

namespace EffectConnect\Marketplaces\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Template;

/**
 * HTML text element with time validations
 * @package EffectConnect\Marketplaces\Block\Adminhtml\Form\Field
 */
class Time extends Template
{
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
        $html = '<input type="text" name="' . $this->getName() . '" id="' . $this->getId() . '" ';
        $html .= 'data-validate="{\'time\':true,\'validate-length\':true}" ';
        $html .= 'placeholder="' . __('hh:mm') . '" ';
        $html .= 'value="<%- ' . $this->getColumnName() . ' %>" ';
        $html .= 'class="minimum-length-5 maximum-length-5 ' . $this->getClass() . '" ' . $this->getExtraParams() . '/> ';
        return $html;
    }
}
