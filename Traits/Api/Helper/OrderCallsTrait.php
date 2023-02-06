<?php

namespace EffectConnect\Marketplaces\Traits\Api\Helper;

use DateTime;
use EffectConnect\Marketplaces\Enums\Api\FilterTag;
use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Exception\OrderImportFailedException;
use EffectConnect\Marketplaces\Objects\ApiWrapper;
use EffectConnect\Marketplaces\Objects\ConnectionApi;
use EffectConnect\PHPSdk\Core\Model\Filter\HasStatusFilter;
use EffectConnect\PHPSdk\Core\Model\Response\Order as EffectConnectOrder;
use EffectConnect\PHPSdk\Core\Model\Response\OrderListReadResponseContainer;

/**
 * Trait OrderCallsTrait
 * @package EffectConnect\Marketplaces\Traits\Api\Helper
 */
trait OrderCallsTrait
{
    protected $orderListReadTypePaid              = 'paid';
    protected $orderListReadTypeCompletedExternal = 'completed_external';

    /**
     * @param ConnectionApi $connectionApi
     * @return bool
     */
    protected function importOrdersProcedure(ConnectionApi $connectionApi) : bool
    {
        $connection        = $connectionApi->getConnection();
        $logHelper         = $connectionApi->getLogHelper();
        $apiHelper         = $connectionApi->getApiHelper();
        $apiWrapper        = $connectionApi->getApiWrapper();
        $transformerHelper = $apiHelper->getTransformerHelper();

        $logHelper->logOrderImportStarted(intval($connection->getEntityId()));

        $processedOrdersCount = 0;
        foreach ([$this->orderListReadTypePaid, $this->orderListReadTypeCompletedExternal] as $orderListReadType)
        {
            try
            {
                $orders = $this->orderListReadByType($apiWrapper, $orderListReadType);
            }
            catch (ApiCallFailedException $e)
            {
                $logHelper->logOrderImportEnded(intval($connection->getEntityId()), false, $e->getMessage());
                return false;
            }

            if ($orders->getCount() > 0)
            {
                $skippedOrders = [];

                /* @var EffectConnectOrder $effectConnectOrder */
                foreach ($orders->getOrders() as $effectConnectOrder)
                {
                    // Get identifiers for order updates in EffectConnect.
                    $orderIdentifiers = $effectConnectOrder->getIdentifiers();

                    // Import the order to Magento.
                    $processedOrdersCount++;
                    try
                    {
                        $magentoOrder = $transformerHelper->importOrder($this->getConnection(), $effectConnectOrder);
                        if($magentoOrder)
                        {
                            try
                            {
                                // Send feedback to EffectConnect - save Magento order identifiers to EC.
                                $apiWrapper->updateOrder($orderIdentifiers->getEffectConnectNumber(), $magentoOrder->getEntityId(), $magentoOrder->getIncrementId());
                            }
                            catch (ApiCallFailedException $e)
                            {
                                $logHelper->logOrderImportUpdateFailed(
                                    intval($connection->getEntityId()),
                                    $orderIdentifiers->getEffectConnectNumber(),
                                    $magentoOrder->getEntityId(),
                                    $magentoOrder->getIncrementId()
                                );
                            }
                            try
                            {
                                // Send feedback to EffectConnect that we have successfully imported the order.
                                $apiWrapper->updateOrderAddTag($orderIdentifiers->getEffectConnectNumber(), FilterTag::ORDER_IMPORT_SUCCEEDED_TAG());
                            }
                            catch (ApiCallFailedException $e)
                            {
                                $logHelper->logOrderImportAddTagFailed(
                                    intval($connection->getEntityId()),
                                    $orderIdentifiers->getEffectConnectNumber(),
                                    FilterTag::ORDER_IMPORT_SUCCEEDED_TAG()
                                );
                            }
                        }
                        else
                        {
                            // Order was skipped for a reason (add to list of skipped orders to assign the skipped tag in bulk afterwards)
                            $skippedOrders[] = $orderIdentifiers->getEffectConnectNumber();
                        }
                    }
                    catch (OrderImportFailedException $e)
                    {
                        try
                        {
                            // Make a call to EC to identify that the order could not be imported.
                            $apiWrapper->updateOrderAddTag($orderIdentifiers->getEffectConnectNumber(), FilterTag::ORDER_IMPORT_FAILED_TAG());
                        }
                        catch (ApiCallFailedException $e)
                        {
                            $logHelper->logOrderImportAddTagFailed(
                                intval($connection->getEntityId()),
                                $orderIdentifiers->getEffectConnectNumber(),
                                FilterTag::ORDER_IMPORT_FAILED_TAG()
                            );
                        }
                    }
                }

                if (count($skippedOrders) > 0)
                {
                    try
                    {
                        // Make a call to EC to identify that the order import was skipped.
                        $apiWrapper->updateOrdersAddTag($skippedOrders, FilterTag::ORDER_IMPORT_SKIPPED_TAG());
                    }
                    catch (ApiCallFailedException $e)
                    {
                        $logHelper->logOrderImportAddTagFailed(
                            intval($connection->getEntityId()),
                            implode(',', $skippedOrders),
                            FilterTag::ORDER_IMPORT_SKIPPED_TAG()
                        );
                    }
                }
            }
        }

        if ($processedOrdersCount == 0)
        {
            // No orders to fetch.
            $logHelper->logOrderImportNoOrdersAvailable(intval($connection->getEntityId()));
        }

        $logHelper->logOrderImportEnded(intval($connection->getEntityId()), true);
        return true;
    }

    /**
     * @param ApiWrapper $apiWrapper
     * @param string $type
     * @return OrderListReadResponseContainer
     * @throws ApiCallFailedException
     */
    protected function orderListReadByType(ApiWrapper $apiWrapper, string $type) : OrderListReadResponseContainer
    {
        $excludeTags = [FilterTag::ORDER_IMPORT_SUCCEEDED_TAG(), FilterTag::ORDER_IMPORT_FAILED_TAG(), FilterTag::ORDER_IMPORT_SKIPPED_TAG()];

        switch ($type) {
            case $this->orderListReadTypePaid:
                // Fetch orders with status PAID
                $orders = $apiWrapper->orderListRead([HasStatusFilter::STATUS_PAID], $excludeTags);
                break;

            case $this->orderListReadTypeCompletedExternal:
                // Fetch orders with status COMPLETED and external fulfilment tag
                // Implemented since 1-6-2021, let's prevent that order are fetched in retrospective
                $fromDate = new DateTime('2021-06-01 00:00:00');
                $orders   = $apiWrapper->orderListRead([HasStatusFilter::STATUS_COMPLETED], $excludeTags, [FilterTag::EXTERNAL_FULFILMENT_TAG()], $fromDate);
                break;

            default:
                throw new ApiCallFailedException(__('Fetching orders from EffectConnect failed - invalid order list read type.'));
        }

        return $orders;
    }
}