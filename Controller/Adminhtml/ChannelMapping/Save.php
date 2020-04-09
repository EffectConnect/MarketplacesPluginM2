<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping;

use EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping;
use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Class Save
 * @package EffectConnect\Marketplaces\Controller\Adminhtml\ChannelMapping
 */
class Save extends ChannelMapping
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $formData = $this->getRequest()->getPostValue();
            try {
                if (isset($formData['entity_id']) && intval($formData['entity_id']) > 0) {
                    $channelMapping = $this->_channelMappingRepository->getById(intval($formData['entity_id']));
                } else {
                    $channelMapping = $this->_channelMappingRepository->create();
                }

                switch($formData['customer_create'])
                {
                    case '0':
                        // Create customer is set to No? - user may select customer to assign the order to - so customer group can be set to null.
                        $formData['customer_group_id'] = null;
                        break;
                    case '1':
                        // Create customer is set to Yes? - user must select group to add the customer to - so customer can be set to null.
                        $formData['customer_id']       = null;
                        break;
                    case '2': // Use default configuration
                        $formData['customer_group_id'] = null;
                        $formData['customer_id']       = null;
                        break;
                }

                // Process channel mapping data
                $channelMappingData = [
                    'connection_id'       => $formData['connection_id'],
                    'channel_id'          => $formData['channel_id'],
                    'storeview_id'        => $formData['storeview_id'],
                    'external_fulfilment' => $formData['external_fulfilment'],
                    'customer_create'     => $formData['customer_create'],
                    'customer_group_id'   => (trim($formData['customer_group_id']) == '' ? null : $formData['customer_group_id']),
                    'customer_id'         => (!isset($formData['customer_id']) || trim($formData['customer_id']) == '' ? null : $formData['customer_id']), // Can be empty in case of empty multiselect
                    'send_emails'         => $formData['send_emails'],
                    'discount_code'       => $formData['discount_code'],
                    'payment_method'      => (trim($formData['payment_method']) == '' ? null : $formData['payment_method']),
                    'shipping_method'     => (trim($formData['shipping_method']) == '' ? null : $formData['shipping_method']),
                ];
                $channelMapping->addData($channelMappingData);

                // Save the channel mapping
                $this->_channelMappingRepository->save($channelMapping);

                $this->messageManager->addSuccessMessage(__('The channel mapping has been saved.'));
            } catch (AlreadyExistsException $e) {
                $this->messageManager->addErrorMessage(__('The channel mapping could not been saved, because a channel mapping already exists for given combination of connection and channel.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('The channel mapping could not been saved.'));
            }
        }
        $this->_redirect('*/*');
    }
}
