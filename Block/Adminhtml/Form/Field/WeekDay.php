<?php

namespace EffectConnect\Marketplaces\Block\Adminhtml\Form\Field;

use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * HTML select element block with week days
 * @package EffectConnect\Marketplaces\Block\Adminhtml\Form\Field
 */
class WeekDay extends Select
{
    /**
     * @var ListsInterface
     */
    protected $_localeLists;

    /**
     * @param Context $context
     * @param ListsInterface $localeLists
     * @param array $data
     */
    public function __construct(
        Context $context,
        ListsInterface $localeLists,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_localeLists = $localeLists;
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
            $this->setOptions($this->_localeLists->getOptionWeekdays());
        }
        return parent::_toHtml();
    }
}
