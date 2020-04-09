<?php

namespace EffectConnect\Marketplaces\Controller\Adminhtml\Connection;

use EffectConnect\Marketplaces\Controller\Adminhtml\Connection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class TestConnection
 * @package EffectConnect\Marketplaces\Controller\Adminhtml\Connection
 */
class TestConnection extends Connection
{
    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        // Get public / secret key from post.
        $publicKey = $this->getRequest()->getPost('public_key');
        $secretKey = $this->getRequest()->getPost('secret_key');

        // Test the credentials.
        $result = $this->_apiHelper->testCredentials($publicKey, $secretKey);

        // Output result.
        $response = ['success' => $result];
        $resultJson = $this->_resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
