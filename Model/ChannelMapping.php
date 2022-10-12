<?php

namespace EffectConnect\Marketplaces\Model;

use DateTime;
use Exception;
use EffectConnect\Marketplaces\Enums\ExternalFulfilment;
use EffectConnect\Marketplaces\Helper\SettingsHelper;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class ChannelMapping
 * @method string|null getConnectionId()
 * @method string|null getChannelId()
 * @method string|null getStoreviewIdInternal()
 * @method string|null getStoreviewIdExternal()
 * @method string|null getExternalFulfilment()
 * @method string|null getCustomerCreate()
 * @method string|null getCustomerGroupId()
 * @method string|null getCustomerId()
 * @method string|null getSendEmails()
 * @method string|null getDiscountCode()
 * @method string|null getShippingMethod()
 * @method string|null getPaymentMethod()
 * @method Connection setConnectionId(string|null $string)
 * @method Connection setChannelId(string|null $string)
 * @method Connection setStoreviewIdInternal(string|null $string)
 * @method Connection setStoreviewIdExternal(string|null $string)
 * @method Connection setExternalFulfilment(string|null $string)
 * @method Connection setCustomerCreate(string|null $string)
 * @method Connection setCustomerGroupId(string|null $string)
 * @method Connection setCustomerId(string|null $string)
 * @method Connection setSendEmails(string|null $string)
 * @method Connection setDiscountCode(string|null $string)
 * @method Connection setShippingMethod(string|null $string)
 * @method Connection setPaymentMethod(string|null $string)
 * @package EffectConnect\Marketplaces\Model
 */
class ChannelMapping extends AbstractModel
{
    /**
     * @var SettingsHelper
     */
    protected $_settingsHelper;

    /**
     * @var TimezoneInterface
     */
    protected $_timezone;

    /**
     * ChannelMapping constructor.
     * @param Context $context
     * @param Registry $registry
     * @param SettingsHelper $settingsHelper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SettingsHelper $settingsHelper,
        TimezoneInterface $timezone
    ) {
        $this->_init(ResourceModel\ChannelMapping::class);
        parent::__construct($context, $registry);
        $this->_settingsHelper = $settingsHelper;
        $this->_timezone = $timezone;
    }

    /**
     * Merge given channel mapping with default configuration settings.
     * For example the setting 'customer_create' can be set in the default Magento configuration and can be overwritten by the channel mapping.
     *
     * @param int $storeviewId
     * @return bool
     */
    public function getCustomerCreateIncludingConfiguration(int $storeviewId = 0) : bool
    {
        // Use default configuration settings for customer creation in case:
        // - the currently loaded channel mapping data is empty;
        // - or if the current channel mapping has 'customer_create' set to 2 (= 'Use default configuration').
        if ($this->getEntityId() == 0 || $this->getCustomerCreate() == 2) {

            // By default use currently loaded storeview id from model, but this can be overriden by $storeviewId (can be used in case the model data is not loaded yet).
            if ($storeviewId == 0) {
                $storeviewId = $this->getStoreviewId();
            }

            // Get default 'customer_create' setting.
            return boolval($this->_settingsHelper->getOrderImportCustomerCreate(SettingsHelper::SCOPE_STORE, $storeviewId));
        }

        // Just return currently loaded data for 'customer_create'.
        return ($this->getCustomerCreate() == 1);
    }

    /**
     * Merge given channel mapping with default configuration settings.
     * For example the setting 'customer_create' can be set in the default Magento configuration and can be overwritten by the channel mapping.
     *
     * @param int $storeviewId
     * @return int
     */
    public function getCustomerGroupIdIncludingConfiguration(int $storeviewId = 0) : int
    {
        // Use default configuration settings for customer_group_id in case:
        // - the currently loaded channel mapping data is empty;
        // - or if the current channel mapping has 'customer_create' set to 2 (= 'Use default configuration').
        if ($this->getEntityId() == 0 || $this->getCustomerCreate() == 2) {

            // By default use currently loaded storeview id from model, but this can be overriden by $storeviewId (can be used in case the model data is not loaded yet).
            if ($storeviewId == 0) {
                $storeviewId = $this->getStoreviewId();
            }

            // Get default 'customer_group_id' setting.
            return intval($this->_settingsHelper->getOrderImportCustomerGroupId(SettingsHelper::SCOPE_STORE, $storeviewId));
        }

        // Just return currently loaded data for 'customer_group_id'.
        return intval($this->getCustomerGroupId());
    }

    /**
     * Merge given channel mapping with default configuration settings.
     * For example the setting 'send_emails' can be set in the default Magento configuration and can be overwritten by the channel mapping.
     *
     * @param int $storeviewId
     * @return bool
     */
    public function getSendEmailsIncludingConfiguration(int $storeviewId = 0) : bool
    {
        // Use default configuration settings for sending emails in case:
        // - the currently loaded channel mapping data is empty;
        // - or if the current channel mapping has 'send_emails' set to 2 (= 'Use default configuration').
        if ($this->getEntityId() == 0 || $this->getSendEmails() == 2) {

            // By default use currently loaded storeview id from model, but this can be overriden by $storeviewId (can be used in case the model data is not loaded yet).
            if ($storeviewId == 0) {
                $storeviewId = $this->getStoreviewId();
            }

            // Get default 'send_emails' setting.
            return boolval($this->_settingsHelper->getOrderImportSendEmails(SettingsHelper::SCOPE_STORE, $storeviewId));
        }

        // Just return currently loaded data for 'send_emails'.
        return ($this->getSendEmails() == 1);
    }

    /**
     * @param int $storeviewId
     * @return string
     */
    public function getPaymentMethodIncludingConfiguration(int $storeviewId = 0) : string
    {
        // Use default configuration settings for PaymentMethod in case:
        // - the currently loaded channel mapping data is empty;
        // - or if the current channel mapping has 'payment_method' set to NULL (= 'Use default configuration').
        // Because there are no 'empty' payment methods, we can just check for empty 'payment_method'.
        if (empty($this->getPaymentMethod())) {

            // By default use currently loaded storeview id from model, but this can be overriden by $storeviewId (can be used in case the model data is not loaded yet).
            if ($storeviewId == 0) {
                $storeviewId = $this->getStoreviewId();
            }

            // Get default setting.
            return $this->_settingsHelper->getOrderImportPaymentMethod(SettingsHelper::SCOPE_STORE, $storeviewId);
        }

        // Just return currently loaded data.
        return $this->getPaymentMethod();
    }

    /**
     * @param int $storeviewId
     * @param DateTime $orderDate
     * @return string
     */
    public function getShippingMethodIncludingConfiguration(int $storeviewId, DateTime $orderDate) : string
    {
        // Use default configuration settings for ShippingMethod in case:
        // - the currently loaded channel mapping data is empty;
        // - or if the current channel mapping has 'shipping_method' set to NULL (= 'Use default configuration').
        // Because there are no 'empty' shipping methods, we can just check for empty 'shipping_method'.
        if (empty($this->getShippingMethod())) {

            // By default use currently loaded storeview id from model, but this can be overriden by $storeviewId (can be used in case the model data is not loaded yet).
            if ($storeviewId == 0) {
                $storeviewId = $this->getStoreviewId();
            }

            // Get default setting.
            $defaultShippingMethod = $this->_settingsHelper->getOrderImportShippingMethod(SettingsHelper::SCOPE_STORE, $storeviewId);
        } else {
            // Just return currently loaded channel mapping's shipping method.
            $defaultShippingMethod = $this->getShippingMethod();
        }

        // Get shipping method mapping setting.
        $shippingMethodMappings = $this->_settingsHelper->getOrderImportShippingMethodMapping(SettingsHelper::SCOPE_STORE, $storeviewId);
        $shippingMethodMapping = '';

        try {
            $orderDateInMagentoTimezone = $this->_timezone->date($orderDate);

            // First mapping that meets the order date is used.
            foreach ($shippingMethodMappings as $mapping) {
                $weekday        = intval($mapping['weekday'] ?? -1);
                $startTime      = strval($mapping['start_time'] ?? '');
                $endTime        = strval($mapping['end_time'] ?? '');
                $meetsWeekday   = $weekday === intval($orderDateInMagentoTimezone->format('w'));
                $meetsStartTime = empty($startTime) || $orderDateInMagentoTimezone->format('H:i') >= $startTime;
                $meetsEndTime   = empty($endTime) || $orderDateInMagentoTimezone->format('H:i') <= $endTime;
                if ($meetsWeekday && $meetsStartTime && $meetsEndTime) {
                    $shippingMethodMapping = $mapping['shipping_method_id'] ?? '';
                    break;
                }
            }
        } catch (Exception $e) {}

        return !empty($shippingMethodMapping) && !$this->getIgnoreShippingMethodMapping() ? $shippingMethodMapping : $defaultShippingMethod;
    }

    /**
     * @return int
     */
    public function getStoreviewId()
    {
        if ($this->getExternalFulfilment() == ExternalFulfilment::EXTERNAL_ORDERS) {
            return intval($this->getStoreviewIdExternal());
        }
        return intval($this->getStoreviewIdInternal());
    }
}
