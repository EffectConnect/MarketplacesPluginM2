<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ShippingMethods - adapted from Magento\Shipping\Model\Config\Source\Allmethods to allow for changes.
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class ShippingMethods implements OptionSourceInterface
{
    /**
     * The code of the EffectConnect Shipping Method.
     */
    const EFFECTCONNECT_SHIPPING_CODE = 'effectconnect_marketplaces_carrier';

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Config
     */
    protected $_shippingConfig;

    /**
     * ShippingMethods constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $shippingConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $shippingConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_shippingConfig = $shippingConfig;
    }

    /**
     * @param bool $type
     * @return array
     */
    public function toOptionArray($type = false)
    {
        $methods = [];

        $carriers = $this->_shippingConfig->getAllCarriers();
        foreach ($carriers as $carrierCode => $carrierModel) {
            if (!$carrierModel->isActive() && $carrierCode !== static::EFFECTCONNECT_SHIPPING_CODE) {
                continue;
            }
            $carrierMethods = $carrierModel->getAllowedMethods();
            if (!$carrierMethods) {
                continue;
            }
            $carrierTitle = $this->_scopeConfig->getValue(
                'carriers/' . $carrierCode . '/title',
                ScopeInterface::SCOPE_STORE
            );
            $methods[$carrierCode] = ['label' => $carrierTitle, 'value' => []];
            foreach ($carrierMethods as $methodCode => $methodTitle) {
                $methods[$carrierCode]['value'][] = [
                    'value' => $carrierCode . '_' . $methodCode,
                    'label' => $methodTitle,
                ];
            }
        }

        return $methods;
    }
}
