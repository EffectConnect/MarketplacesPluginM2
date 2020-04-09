<?php

namespace EffectConnect\Marketplaces\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Channel
 * @method string|null getConnectionId()
 * @method string|null getEcChannelId()
 * @method string|null getEcChannelType()
 * @method string|null getEcChannelSubtype()
 * @method string|null getEcChannelTitle()
 * @method string|null getEcChannelLanguage()
 * @method Channel setConnectionId(string|null $string)
 * @method Channel setEcChannelId(string|null $string)
 * @method Channel setEcChannelType(string|null $string)
 * @method Channel setEcChannelSubtype(string|null $string)
 * @method Channel setEcChannelTitle(string|null $string)
 * @method Channel setEcChannelLanguage(string|null $string)
 * @package EffectConnect\Marketplaces\Model
 */
class Channel extends AbstractModel
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Channel::class);
    }
}
