<?php

namespace EffectConnect\Marketplaces\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class ConnectionStoreview
 * @method string|null getConnectionId()
 * @method string|null getStoreviewId()
 * @method string|null getLanguageCode()
 * @method ConnectionStoreview setConnectionId(string|null $string)
 * @method ConnectionStoreview setStoreviewId(string|null $string)
 * @method ConnectionStoreview setLanguageCode(string|null $string)
 * @package EffectConnect\Marketplaces\Model
 */
class ConnectionStoreview extends AbstractModel
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(ResourceModel\ConnectionStoreview::class);
    }
}
