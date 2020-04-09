<?php

namespace EffectConnect\Marketplaces\Model\Method;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class EffectConnectPayment
 * @package EffectConnect\Marketplaces\Model\Method
 */
class EffectConnectPayment extends AbstractMethod
{
    /**
     * @var string
     */
    protected $_code        = 'effectconnect_marketplaces_payment';

    /**
     * @var bool
     */
    protected $_isOffline   = true;
}