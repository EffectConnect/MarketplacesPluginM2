<?php

namespace EffectConnect\Marketplaces\Model\Config\Backend;

/**
 * Class OrderImportSchedule
 * @package EffectConnect\Marketplaces\Model\Config\Backend
 */
class OrderImportSchedule extends AbstractSchedule
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH = 'crontab/default/jobs/effectconnect_marketplaces_import_orders/schedule/cron_expr';

    /**
     * Cron model path
     */
    const CRON_MODEL_PATH = 'crontab/default/jobs/effectconnect_marketplaces_import_orders/run/model';

    /**
     * Default cron schedule
     */
    const DEFAULT_CRON_SCHEDULE = '*/5 * * * *';
}