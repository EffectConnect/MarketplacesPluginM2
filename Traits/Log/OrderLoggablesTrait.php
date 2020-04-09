<?php

namespace EffectConnect\Marketplaces\Traits\Log;

use EffectConnect\Marketplaces\Enums\LogCode;
use EffectConnect\Marketplaces\Enums\LogSubjectType;
use EffectConnect\Marketplaces\Enums\LogType;
use EffectConnect\Marketplaces\Enums\Process;
use EffectConnect\Marketplaces\Model\ChannelMapping;
use EffectConnect\Marketplaces\Objects\Loggable;
use EffectConnect\PHPSdk\Core\Model\Response\Order as EffectConnectOrder;
use Magento\Sales\Model\Order;

/**
 * Trait OrderLoggablesTrait
 * @package EffectConnect\Marketplaces\Traits\Log
 */
trait OrderLoggablesTrait
{
    /**
     * Log when the order import has started.
     *
     * @param int $connectionId
     * @return bool
     */
    public function logOrderImportStarted(int $connectionId) : bool
    {
        $loggable = new Loggable(
            LogType::INFO(),
            LogCode::ORDER_IMPORT_HAS_STARTED(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Importing the connection %s orders from EffectConnect Marketplaces has started.',
            [
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::CONNECTION(),
            $connectionId
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when the order import has ended.
     *
     * @param int $connectionId
     * @param bool $succeeded
     * @param string $message
     * @return bool
     */
    public function logOrderImportEnded(int $connectionId, bool $succeeded, string $message = '') : bool
    {
        $loggable = new Loggable(
            $succeeded ? LogType::SUCCESS() : LogType::FATAL(),
            LogCode::ORDER_IMPORT_HAS_ENDED(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        if (!empty($message))
        {
            $loggable->setFormattedMessage(
                'Importing the connection %s orders from EffectConnect Marketplaces has ended with message [%s].',
                [
                    $connectionId,
                    $message
                ]
            );
        }
        else
        {
            $loggable->setFormattedMessage(
                'Importing the connection %s orders from EffectConnect Marketplaces has ended.',
                [
                    $connectionId
                ]
            );
        }

        $loggable->setSubject(
            LogSubjectType::CONNECTION(),
            $connectionId
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param string $effectConnectNumber
     * @param string $tag
     * @return mixed
     */
    public function logOrderImportAddTagFailed(int $connectionId, string $effectConnectNumber, string $tag)
    {
        $loggable = new Loggable(
            LogType::ERROR(),
            LogCode::ORDER_IMPORT_ADD_TAG_FAILED(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        $loggable->setMessage(__('Adding tag to EffectConnect failed after importing an order.'));

        $loggable->setPayload(json_encode([
            'effectConnectNumber' => $effectConnectNumber,
            'tag'                 => $tag,
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param string $effectConnectNumber
     * @param string $magentoOrderId
     * @param string $magentoOrderNumber
     * @return bool
     */
    public function logOrderImportUpdateFailed(int $connectionId, string $effectConnectNumber, string $magentoOrderId, string $magentoOrderNumber) : bool
    {
        $loggable = new Loggable(
            LogType::FATAL(),
            LogCode::ORDER_IMPORT_UPDATE_FAILED(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        $loggable->setMessage(__('Order update to EffectConnect failed after importing an order.'));

        $loggable->setPayload(json_encode([
            'effectConnectNumber' => $effectConnectNumber,
            'magentoOrderId'      => $magentoOrderId,
            'magentoOrderNumber'  => $magentoOrderNumber
        ]));

        $loggable->setSubject(
            LogSubjectType::ORDER(),
            $magentoOrderId
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param ChannelMapping $channelMapping
     * @return bool
     */
    public function logOrderImportFailedOnStoreIdAndWebsiteIdMatch(int $connectionId, ChannelMapping $channelMapping) : bool
    {
        $loggable = new Loggable(
            LogType::FATAL(),
            LogCode::ORDER_IMPORT_FAILED(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        $loggable->setMessage(__('Order import failed: the website selected in the connection does not match the storeview that is used in the channel mapping.'));

        $loggable->setPayload(json_encode([
            'channelMapping' => $channelMapping->getData()
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param ChannelMapping $channelMapping
     * @return bool
     */
    public function logOrderImportFailedOnStoreId(int $connectionId, ChannelMapping $channelMapping) : bool
    {
        $loggable = new Loggable(
            LogType::FATAL(),
            LogCode::ORDER_IMPORT_FAILED(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        $loggable->setMessage(__('Order import failed when fetching store ID from order.'));

        $loggable->setPayload(json_encode([
            'channelMapping' => $channelMapping->getData()
        ]));

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param EffectConnectOrder $effectConnectOrder
     * @param string $message
     * @return bool
     */
    public function logOrderImportFailed(int $connectionId, EffectConnectOrder $effectConnectOrder, string $message) : bool
    {
        $loggable = new Loggable(
            LogType::FATAL(),
            LogCode::ORDER_IMPORT_FAILED(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        $loggable->setSubject(
            LogSubjectType::CONNECTION(),
            $connectionId
        );

        $loggable->setMessage($message);

        $loggable->setPayload(
            json_encode(['EffectConnect Order' => $effectConnectOrder])
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param string $message
     * @return bool
     */
    public function logOrdersImportConnectionFailed(int $connectionId, string $message) : bool
    {
        $loggable = new Loggable(
            LogType::FATAL(),
            LogCode::ORDER_IMPORT_FAILED(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        $loggable->setSubject(
            LogSubjectType::CONNECTION(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Order import from EffectConnect Marketplaces has failed with message [%s].',
            [
                $message
            ]
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param string $message
     * @return bool
     */
    public function logOrderImportAddDiscountCodeFailed(int $connectionId, string $message) : bool
    {
        $loggable = new Loggable(
            LogType::WARNING(),
            LogCode::ORDER_IMPORT_DISCOUNT_CODE_FAILED(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        $loggable->setMessage($message);

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param string $message
     * @return bool
     */
    public function logOrderImportSendOrderEmailFailed(int $connectionId, string $message) : bool
    {
        $loggable = new Loggable(
            LogType::WARNING(),
            LogCode::ORDER_IMPORT_SEND_ORDER_EMAIL_FAILED(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        $loggable->setMessage($message);

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param string $effectConnectOrderNumber
     * @param Order $order
     * @return bool
     */
    public function logOrderImportAlreadyExists(int $connectionId, string $effectConnectOrderNumber, Order $order) : bool
    {
        $loggable = new Loggable(
            LogType::NOTICE(),
            LogCode::ORDER_IMPORT_ALREADY_EXISTS(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        $loggable->setPayload(
            'EffectConnect Order Number: ' . $effectConnectOrderNumber
        );

        $loggable->setSubject(
            LogSubjectType::ORDER(),
            $order->getId()
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * @param int $connectionId
     * @param bool $effectConnectOrderIsExternalFulfilled
     * @param string $externalFulfilmentSetting
     * @return bool
     */
    public function logOrderImportSkippedByExternalFulfilment(int $connectionId, bool $effectConnectOrderIsExternalFulfilled, string $externalFulfilmentSetting) : bool
    {
        $loggable = new Loggable(
            LogType::NOTICE(),
            LogCode::ORDER_IMPORT_SKIPPED(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Skipped importing EffectConnect order because of external fulfilment setting (%s).',
            [
                $externalFulfilmentSetting
            ]
        );

        $loggable->setPayload('EffectConnect Order Is External Fulfilled: ' . intval($effectConnectOrderIsExternalFulfilled));

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when the order import has succeeded.
     *
     * @param int $connectionId
     * @param string $effectConnectOrderNumber
     * @param int $magentoOrderId
     * @return bool
     */
    public function logOrderImportSucceeded(int $connectionId, string $effectConnectOrderNumber, int $magentoOrderId) : bool
    {
        $loggable = new Loggable(
            LogType::SUCCESS(),
            LogCode::ORDER_IMPORT_SUCCEEDED(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'Importing EffectConnect order %s succeeded with id %s.',
            [
                $effectConnectOrderNumber,
                $magentoOrderId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::ORDER(),
            $magentoOrderId
        );

        return $this->insertLogItem($loggable);
    }

    /**
     * Log when there are no orders to import.
     *
     * @param int $connectionId
     * @return bool
     */
    public function logOrderImportNoOrdersAvailable(int $connectionId) : bool
    {
        $loggable = new Loggable(
            LogType::INFO(),
            LogCode::ORDER_IMPORT_NO_ORDERS_AVAILABLE(),
            Process::IMPORT_ORDERS(),
            $connectionId
        );

        $loggable->setFormattedMessage(
            'There are no orders available to import from EffectConnect Marketplaces (connection %s).',
            [
                $connectionId
            ]
        );

        $loggable->setSubject(
            LogSubjectType::CONNECTION(),
            $connectionId
        );

        return $this->insertLogItem($loggable);
    }
}