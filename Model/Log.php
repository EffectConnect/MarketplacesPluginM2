<?php

namespace EffectConnect\Marketplaces\Model;

use Magento\Framework\Model\AbstractModel;
use EffectConnect\Marketplaces\Model\ResourceModel\Log as LogResourceModel;

/**
 * Class Log
 * @method string|null getType()
 * @method string|null getCode()
 * @method string|null getProcess()
 * @method string|null getConnectionId()
 * @method string|null getSubjectType()
 * @method string|null getSubjectId()
 * @method string|null getMessage()
 * @method string|null getPayload()
 * @method string|null getOccurredAt()
 * @method Log setType(string|null $string)
 * @method Log setCode(string|null $string)
 * @method Log setProcess(string|null $string)
 * @method Log setConnectionId(string|null $string)
 * @method Log setSubjectType(string|null $string)
 * @method Log setSubjectId(string|null $string)
 * @method Log setMessage(string|null $string)
 * @method Log setPayload(string|null $string)
 * @method Log setOccurredAt(string|null $string)
 * @package EffectConnect\Marketplaces\Model
 */
class Log extends AbstractModel
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(LogResourceModel::class);
    }
}
