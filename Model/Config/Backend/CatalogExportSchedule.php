<?php

namespace EffectConnect\Marketplaces\Model\Config\Backend;

/**
 * Class CatalogExportSchedule
 * @package EffectConnect\Marketplaces\Model\Config\Backend
 */
class CatalogExportSchedule extends AbstractSchedule
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH = 'crontab/effectconnect_marketplaces/jobs/effectconnect_marketplaces_export_catalog/schedule/cron_expr';

    /**
     * Cron model path
     */
    const CRON_MODEL_PATH = 'crontab/effectconnect_marketplaces/jobs/effectconnect_marketplaces_export_catalog/run/model';

    /**
     * Default cron schedule
     */
    const DEFAULT_CRON_SCHEDULE = '0 20 * * *';
}