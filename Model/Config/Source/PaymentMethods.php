<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Payment\Helper\Data;

/**
 * Class PaymentMethods - adapted from Magento\Payment\Model\Config\Source\Allmethods to allow for changes.
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class PaymentMethods implements OptionSourceInterface
{
    /**
     * @var Data
     */
    protected $_paymentData;

    /**
     * PaymentMethods constructor.
     * @param Data $paymentData
     */
    public function __construct(Data $paymentData)
    {
        $this->_paymentData = $paymentData;
    }

    /**
     * @param bool $type
     * @return array
     */
    public function toOptionArray($type = false)
    {
        return $this->_paymentData->getPaymentMethodList(true, true, true);
    }
}