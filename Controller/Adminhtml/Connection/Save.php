<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml\Connection;

use EffectConnect\Marketplaces\Controller\Adminhtml\Connection;
use EffectConnect\Marketplaces\Exception\ConnectionSaveDuplicateLanguageException;
use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Save
 * @package EffectConnect\Marketplaces\Controller\Adminhtml\Connection
 */
class Save extends Connection
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getPostValue()) {
            $formData = $this->getRequest()->getPostValue();

            try {
                if (isset($formData['entity_id']) && intval($formData['entity_id']) > 0) {
                    $connection = $this->_connectionRepository->getById(intval($formData['entity_id']));
                } else {
                    $connection = $this->_connectionRepository->create();
                }

                // Process connection data.
                $connectionData = [
                    'is_active'                 => $formData['is_active'],
                    'name'                      => $formData['name'],
                    'public_key'                => $formData['public_key'],
                    'secret_key'                => $formData['secret_key'],
                    'website_id'                => $formData['website_id'],
                    'base_storeview_id'         => $formData['base_storeview_id'],
                    'image_url_storeview_id'    => $formData['image_url_storeview_id'],
                ];
                $connection->addData($connectionData);

                // Process storeview mapping data.
                $languageCodes = [];
                $connectionStoreviews = [];
                $connectionStoreviewsFormdata = isset($formData['storeview_mapping']) ? $formData['storeview_mapping'] : [];
                foreach ($connectionStoreviewsFormdata as $connectionStoreviewFormdata) {

                    // Only save storeviews for selected website.
                    if ($connectionStoreviewFormdata['website_id'] == $formData['website_id']) {

                        // Selected languages should be unique.
                        if (in_array($connectionStoreviewFormdata['language_code'], $languageCodes)) {
                            throw new ConnectionSaveDuplicateLanguageException(__('Each language can only be selected once.'));
                        }

                        $connectionStoreview = $this->_connectionStoreviewRepository->create();
                        $connectionStoreview->addData($connectionStoreviewFormdata);
                        $connectionStoreviews[] = $connectionStoreview;

                        // Each language code may only occur once (empty languages codes are allowed multiple times, because that means current storeview is not taken into account when exporting the catalog).
                        if (!empty($connectionStoreviewFormdata['language_code'])) {
                            $languageCodes[] = $connectionStoreviewFormdata['language_code'];
                        }
                    }
                }

                // Save the connection and its storeview mappings.
                $this->_connectionRepository->save($connection, $connectionStoreviews);
                $this->messageManager->addSuccessMessage(__('The connection has been saved.'));
                $resultRedirect->setPath('*/*/');
            } catch (ConnectionSaveDuplicateLanguageException $e) {
                $this->messageManager->addErrorMessage(__('The connection could not been saved. Message: %1.', $e->getMessage()));
                if (isset($formData['entity_id']) && intval($formData['entity_id']) > 0) {
                    $resultRedirect->setPath('*/*/edit', ['entity_id' => $formData['entity_id']]); // Stay on this edit page.
                } else {
                    $resultRedirect->setPath('*/*/edit'); // Stay on this add page.
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('The connection could not been saved.'));
                $resultRedirect->setPath('*/*/');
            }
        }

        return $resultRedirect;
    }
}
