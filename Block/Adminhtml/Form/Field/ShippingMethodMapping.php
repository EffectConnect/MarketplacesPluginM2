<?php

namespace EffectConnect\Marketplaces\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class ShippingMethodMapping
 * @package EffectConnect\Marketplaces\Block\Adminhtml\Form\Field
 */
class ShippingMethodMapping extends AbstractFieldArray
{
    /**
     * @var BlockInterface
     */
    protected $_shippingMethodGroupRenderer;

    /**
     * @var BlockInterface
     */
    protected $_weekDayGroupRenderer;

    /**
     * @var BlockInterface
     */
    protected $_timeRenderer;

    /**
     * @return BlockInterface
     * @throws LocalizedException
     */
    protected function _getShippingMethodGroupRenderer(): BlockInterface
    {
        if (!$this->_shippingMethodGroupRenderer) {
            $this->_shippingMethodGroupRenderer = $this->getLayout()->createBlock(
                ShippingMethod::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_shippingMethodGroupRenderer->setClass('shipping_method_select admin__control-select');
        }
        return $this->_shippingMethodGroupRenderer;
    }

    /**
     * @return BlockInterface
     * @throws LocalizedException
     */
    protected function _getWeekDayGroupRenderer(): BlockInterface
    {
        if (!$this->_weekDayGroupRenderer) {
            $this->_weekDayGroupRenderer = $this->getLayout()->createBlock(
                WeekDay::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_weekDayGroupRenderer->setClass('week_day_select admin__control-select');
        }
        return $this->_weekDayGroupRenderer;
    }

    /**
     * @return BlockInterface
     * @throws LocalizedException
     */
    protected function _getTimeRenderer(): BlockInterface
    {
        if (!$this->_timeRenderer) {
            $this->_timeRenderer = $this->getLayout()->createBlock(Time::class);
            $this->_timeRenderer->setClass('admin__control-text');
        }
        return $this->_timeRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'shipping_method_id',
            [
                'label'    => __('Shipping method'),
                'renderer' => $this->_getShippingMethodGroupRenderer()
            ]
        );
        $this->addColumn(
            'weekday',
            [
                'label'    => __('Weekday'),
                'renderer' => $this->_getWeekDayGroupRenderer()
            ]
        );
        $this->addColumn(
            'start_time',
            [
                'label'    => __('Start time'),
                'renderer' => $this->_getTimeRenderer()
            ]
        );
        $this->addColumn(
            'end_time',
            [
                'label' => __('End time'),
                'renderer' => $this->_getTimeRenderer()
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add new mapping');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getShippingMethodGroupRenderer()->calcOptionHash($row->getData('shipping_method_id'))] =
            'selected="selected"';
        $optionExtraAttr['option_' . $this->_getWeekDayGroupRenderer()->calcOptionHash($row->getData('weekday'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
