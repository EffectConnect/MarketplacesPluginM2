<?php

namespace EffectConnect\Marketplaces\Traits\Api\Helper;

use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Exception\OrderImportFailedException;
use EffectConnect\Marketplaces\Objects\ConnectionApi;
use EffectConnect\PHPSdk\Core\Model\Response\Order as EffectConnectOrder;

/**
 * Trait OrderCallsTrait
 * @package EffectConnect\Marketplaces\Traits\Api\Helper
 */
trait OrderCallsTrait
{
    protected $orderImportFailedTag    = 'order_import_failed';
    protected $orderImportSucceededTag = 'order_import_succeeded';

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

        try
        {
            $excludeTags = [$this->orderImportSucceededTag, $this->orderImportFailedTag];
            $orders = $apiWrapper->orderListRead(true, $excludeTags);
        }
        catch (ApiCallFailedException $e)
        {
            $logHelper->logOrderImportEnded(intval($connection->getEntityId()), false, $e->getMessage());
            return false;
        }

        $processedOrdersCount = 0;
        if ($orders->getCount() > 0)
        {
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
                            $apiWrapper->updateOrderAddTag($orderIdentifiers->getEffectConnectNumber(), $this->orderImportSucceededTag);
                        }
                        catch (ApiCallFailedException $e)
                        {
                            $logHelper->logOrderImportAddTagFailed(
                                intval($connection->getEntityId()),
                                $orderIdentifiers->getEffectConnectNumber(),
                                $this->orderImportSucceededTag
                            );
                        }
                    }
                }
                catch (OrderImportFailedException $e)
                {
                    try
                    {
                        // Make a call to EC to identify that the order could not be imported.
                        $apiWrapper->updateOrderAddTag($orderIdentifiers->getEffectConnectNumber(), $this->orderImportFailedTag);
                    }
                    catch (ApiCallFailedException $e)
                    {
                        $logHelper->logOrderImportAddTagFailed(
                            intval($connection->getEntityId()),
                            $orderIdentifiers->getEffectConnectNumber(),
                            $this->orderImportFailedTag
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
}