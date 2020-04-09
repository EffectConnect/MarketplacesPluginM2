<?php

namespace EffectConnect\Marketplaces\Objects;

use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Traits\Api\Wrapper\ChannelCallsTrait;
use EffectConnect\Marketplaces\Traits\Api\Wrapper\LogCallsTrait;
use EffectConnect\Marketplaces\Traits\Api\Wrapper\OrderCallsTrait;
use EffectConnect\Marketplaces\Traits\Api\Wrapper\ProductCallsTrait;
use EffectConnect\PHPSdk\ApiCall;
use EffectConnect\PHPSdk\Core;
use EffectConnect\PHPSdk\Core\Exception\InvalidKeyException;
use EffectConnect\PHPSdk\Core\Helper\Keychain;
use EffectConnect\PHPSdk\Core\Interfaces\ResponseContainerInterface;
use EffectConnect\PHPSdk\Core\Model\Response\Response;

/**
 * Class Api
 * @package EffectConnect\Marketplaces\Objects
 */
class ApiWrapper
{
    use OrderCallsTrait,
        ChannelCallsTrait,
        ProductCallsTrait,
        LogCallsTrait;

    /**
     * @var string
     */
    protected $_publicKey;

    /**
     * @var string
     */
    protected $_secretKey;

    /**
     * @var Core
     */
    protected $_sdkCore;

    /**
     * Api constructor.
     *
     * @param string $publicKey
     * @param string $secretKey
     * @throws InvalidKeyException
     */
    public function __construct(
        string $publicKey,
        string $secretKey
    ) {
        $this->_publicKey       = $publicKey;
        $this->_secretKey       = $secretKey;

        $this->initializeSdkCore();
    }

    /**
     * @throws InvalidKeyException
     */
    protected function initializeSdkCore()
    {
        $this->_sdkCore = new Core(
            (new Keychain())
                ->setPublicKey($this->_publicKey)
                ->setSecretKey($this->_secretKey)
        );
    }

    /**
     * @return Core
     */
    public function getSdkCore() : Core
    {
        return $this->_sdkCore;
    }

    /**
     * @param ApiCall $apiCall
     * @return ResponseContainerInterface
     * @throws ApiCallFailedException
     */
    protected function getResult(ApiCall $apiCall)
    {
        if (!$apiCall->isSuccess())
        {
            $errorMessageString = '[' . implode('] [', $apiCall->getErrors()) . ']';
            throw new ApiCallFailedException(__('EffectConnect API call failed with message(s) %1.', $errorMessageString));
        }

        $response = $apiCall->getResponseContainer();
        $result = $response->getResponse()->getResult();

        // Successful response?
        if ($result == Response::STATUS_FAILURE)
        {
            $errorMessages = [];
            foreach ($response->getErrorContainer()->getErrorMessages() as $errorMessage)
            {
                $errorMessages[] = vsprintf('%s. Code: %s. Message: %s', [
                    $errorMessage->getSeverity(),
                    $errorMessage->getCode(),
                    $errorMessage->getMessage()
                ]);
            }
            $errorMessageString = '[' . implode('] [', $errorMessages) . ']';
            throw new ApiCallFailedException(__('EffectConnect API call failed with message(s) %1.', $errorMessageString));
        }

        return $response->getResponse()->getResponseContainer();
    }
}