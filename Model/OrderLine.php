<?php

namespace EffectConnect\Marketplaces\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class OrderLine
 * @method string|null getQuoteItemId()
 * @method string|null getEcOrderLineId()
 * @method string|null getShipmentId()
 * @method string|null getTrackId()
 * @method string|null getTrackExportedAt()
 * @method OrderLine setQuoteItemId(string|null $string)
 * @method OrderLine setEcOrderLineId(string|null $string)
 * @method OrderLine setShipmentId(string|null $string)
 * @method OrderLine setTrackId(string|null $string)
 * @method OrderLine setTrackExportedAt(string|null $string)
 * @package EffectConnect\Marketplaces\Model
 */
class OrderLine extends AbstractModel
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(ResourceModel\OrderLine::class);
    }
}
