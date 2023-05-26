<?php

namespace EffectConnect\Marketplaces\Helper;

use EffectConnect\Marketplaces\Exception\SettingNotDefinedException;
use EffectConnect\Marketplaces\Interfaces\SettingPathsInterface;
use EffectConnect\Marketplaces\Model\Config\Backend\ShippingMethodMapping;
use Laminas\Filter\Word\CamelCaseToUnderscore;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * This helper class helps obtaining settings from the store configuration.
 *
 * Class SettingsHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class SettingsHelper extends AbstractHelper implements ScopeInterface, SettingPathsInterface
{
    /**
     * The path prefix for the EffectConnect Marketplaces module.
     */
    const MODULE_PATH = "effectconnect_marketplaces/";

    /**
     * @var ShippingMethodMapping
     */
    protected $shippingMethodMapping;

    /**
     * @param Context $context
     * @param ShippingMethodMapping $shippingMethodMapping
     */
    public function __construct(Context $context, ShippingMethodMapping $shippingMethodMapping)
    {
        parent::__construct($context);
        $this->shippingMethodMapping = $shippingMethodMapping;
    }

    /**
     * Get a setting for a certain scope and scope entity.
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return mixed
     */
    public function get(string $path, string $scope = self::SCOPE_STORE, int $scopeId = 0)
    {
        return $this->scopeConfig->getValue(self::MODULE_PATH . $path, $scope, $scopeId);
    }

    /**
     * @param string $scope
     * @param int $scopeId
     * @return array
     */
    public function getOrderImportShippingMethodMapping(string $scope = self::SCOPE_STORE, int $scopeId = 0): array
    {
        $stringValue = $this->get(static::SETTING_ORDER_IMPORT_SHIPPING_METHOD_MAPPING, $scope, $scopeId);
        $this->shippingMethodMapping->setValue($stringValue);
        $this->shippingMethodMapping->afterLoad();
        $arrayValue = $this->shippingMethodMapping->getValue();
        return is_array($arrayValue) ? $arrayValue : [];
    }

    /**
     * Get a setting for a certain store.
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    public function getStoreSetting(string $path, int $storeId)
    {
        return $this->get($path, static::SCOPE_STORE, $storeId);
    }

    /**
     * Magic call method to get a certain setting using the called method name.
     * For example: getConnectionShopKey returns the value for the SETTING_CONNECTION_SHOP_KEY setting.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws SettingNotDefinedException
     */
    public function __call(string $name, array $arguments)
    {
        $scope      = !isset($arguments[0]) || is_int($arguments[0]) ? static::SCOPE_STORE : strval($arguments[0]);
        $scopeId    = isset($arguments[0]) && is_int($arguments[0]) ? $arguments[0] : intval($arguments[1] ?? 0);
        $setting    = $this->callNameToConstant($name);

        if (!defined('static::' . $setting)) {
            throw new SettingNotDefinedException(__('%1 is not defined in EffectConnect\Marketplaces\Interfaces\SettingPathsInterface', $setting));
        }

        return $this->get(constant(static::class . '::' . $setting), $scope, $scopeId);
    }

    /**
     * Convert the call name to the constant name of a setting.
     * For example: ConnectionShopKey get's converted to CONNECT_SHOP_KEY.
     *
     * @param string $callName
     * @return string
     */
    protected function callNameToConstant(string $callName) : string
    {
        return 'SETTING_' . strtoupper((new CamelCaseToUnderscore())->filter(ltrim($callName, 'get')));
    }
}
