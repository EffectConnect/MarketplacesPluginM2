<?php

namespace EffectConnect\Marketplaces\Model\Config\Backend;

/**
 * Class OfferExportSchedule
 * @package EffectConnect\Marketplaces\Model\Config\Backend
 */
class OfferExportSchedule extends AbstractSchedule
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH = 'crontab/effectconnect_marketplaces/jobs/effectconnect_marketplaces_export_offers/schedule/cron_expr';

    /**
     * Cron model path
     */
    const CRON_MODEL_PATH = 'crontab/effectconnect_marketplaces/jobs/effectconnect_marketplaces_export_offers/run/model';

    /**
     * Default cron schedule
     */
    const DEFAULT_CRON_SCHEDULE = '0 * * * *';
}