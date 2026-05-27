<?php

namespace EffectConnect\Marketplaces\Helper;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Store\Model\App\Emulation;

class FrontendStoreContextHelper extends AbstractHelper
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @param Context $context
     * @param State $appState
     * @param Emulation $appEmulation
     */
    public function __construct(
        Context $context,
        State $appState,
        Emulation $appEmulation
    ) {
        parent::__construct($context);

        $this->appState = $appState;
        $this->appEmulation = $appEmulation;
    }

    /**
     * When running imports/exports, for some methods Magento always expects some application context, which are normally
     * available within frontend requests, but not in CLI commands.
     * So use this helper to run scripts in:
     * - frontend context (emulateAreaCode)
     * - specific store context (startEnvironmentEmulation)
     *
     * Example:
     * $result = $this->frontendStoreContextHelper->emulateAreaCode(function () {
     *     return $this->process();
     * });
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     * @throws Exception
     */
    public function emulateAreaCode(callable $callback)
    {
        return $this->appState->emulateAreaCode(
            Area::AREA_FRONTEND,
            $callback
        );
    }

    /**
     * @param int $storeId
     * @return void
     */
    public function startEnvironmentEmulation(int $storeId)
    {
        $this->appEmulation->startEnvironmentEmulation($storeId);
    }

    /**
     * @return void
     */
    public function stopEnvironmentEmulation()
    {
        $this->appEmulation->stopEnvironmentEmulation();
    }
}