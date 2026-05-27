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
     * $result = $this->frontendStoreContextHelper->run($storeId, function () {
     *     return $this->process();
     * });
     *
     * @template T
     * @param int $storeId
     * @param callable(): T $callback
     * @return T
     * @throws Exception
     */
    public function run(int $storeId, callable $callback)
    {
        return $this->appState->emulateAreaCode(
            Area::AREA_FRONTEND,
            function () use ($storeId, $callback) {
                $this->appEmulation->startEnvironmentEmulation($storeId);
                try {
                    return $callback();
                } finally {
                    $this->appEmulation->stopEnvironmentEmulation();
                }
            }
        );
    }
}