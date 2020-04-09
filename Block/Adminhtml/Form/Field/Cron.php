<?php

namespace EffectConnect\Marketplaces\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Cron
 * @package EffectConnect\Marketplaces\Block\Adminhtml\Form\Field
 */
class Cron extends Field
{
    /**
     * @var string
     */
    protected $_template        = 'EffectConnect_Marketplaces::system/config/cron.phtml';

    /**
     * @var null|AbstractElement
     */
    public $element             = null;

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element) {
        $this->element = $element;
        return parent::_getElementHtml($element) . $this->_toHtml();
    }
}