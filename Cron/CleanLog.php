<?php

namespace EffectConnect\Marketplaces\Cron;

use EffectConnect\Marketplaces\Helper\LogHelper;

/**
 * Class CleanLog
 * @package EffectConnect\Marketplaces\Cron
 */
class CleanLog {
    /**
     * @var LogHelper
     */
    protected $_logHelper;

    /**
     * CleanLog constructor.
     *
     * @param LogHelper $logHelper
     */
    public function __construct(LogHelper $logHelper)
    {
        $this->_logHelper = $logHelper;
    }

    /**
     * Executes when called by the cronjob.
     */
    public function execute()
    {
        $this->_logHelper->cleanLog();
    }
}