<?php

namespace EffectConnect\Marketplaces\Enums;

/**
 * Class LogSubjectType
 * @package EffectConnect\Marketplaces\Enums
 * @method static LogSubjectType PRODUCT()
 * @method static LogSubjectType ORDER()
 * @method static LogSubjectType SHIPMENT()
 * @method static LogSubjectType CONNECTION()
 */
class LogSubjectType extends AbstractEnum
{
    /**
     * Product subject type.
     */
    const PRODUCT       = 'product';

    /**
     * Order subject type.
     */
    const ORDER         = 'order';

    /**
     * Shipment subject type.
     */
    const SHIPMENT      = 'shipment';

    /**
     * Connection subject type.
     */
    const CONNECTION    = 'connection';

    /**
     * Get the path used for generating a URL.
     *
     * @return string
     */
    public function getUrlPath() : string
    {
        switch ($this->getValue()) {
            case static::PRODUCT:
                return 'catalog/product/edit';
            case static::ORDER:
                return 'sales/order/view';
            case static::SHIPMENT:
                return 'sales/shipment/view';
            case static::CONNECTION:
                return 'ec_marketplaces/connection/edit';
        }
    }

    /**
     * Get the id parameter name used for generating a URL.
     *
     * @return string
     */
    public function getIdName() : string
    {
        switch ($this->getValue()) {
            case static::PRODUCT:
                return 'id';
            case static::ORDER:
                return 'order_id';
            case static::SHIPMENT:
                return 'shipment_id';
            case static::CONNECTION:
                return 'entity_id';
        }
    }

    /**
     * Get the path used for generating a URL.
     *
     * @param string $url
     * @param string|int $id
     * @return string
     */
    public function getLinkHtml(string $url, $id) : string
    {
        return html_entity_decode('<a href="' . $url . '">' . $this->getLabel() . ' (' . __('ID') . ': ' . $id . ')</a>');
    }
}