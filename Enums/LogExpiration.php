<?php

namespace EffectConnect\Marketplaces\Enums;

/**
 * Class LogExpiration
 * @package EffectConnect\Marketplaces\Enums
 * @method static LogExpiration ONE_DAY()
 * @method static LogExpiration THREE_DAYS()
 * @method static LogExpiration ONE_WEEK()
 * @method static LogExpiration TWO_WEEKS()
 * @method static LogExpiration ONE_MONTH()
 */
class LogExpiration extends AbstractEnum
{
    /**
     * 1 day log expiration.
     */
    const ONE_DAY       = 'one_day';

    /**
     * 3 days log expiration.
     */
    const THREE_DAYS    = 'three_days';

    /**
     * 1 week log expiration.
     */
    const ONE_WEEK      = 'one_week';

    /**
     * 2 weeks log expiration.
     */
    const TWO_WEEKS     = 'two_weeks';

    /**
     * 1 month log expiration.
     */
    const ONE_MONTH     = 'one_month';

    /**
     * Get the seconds representing the expiration time (in seconds).
     *
     * @return int
     */
    public function getSeconds() : int
    {
        switch ($this->getValue()) {
            case static::ONE_DAY:
                return (1 * 24 * 60 * 60);
            case static::THREE_DAYS:
            default:
                return (3 * 24 * 60 * 60);
            case static::ONE_WEEK:
                return (7 * 24 * 60 * 60);
            case static::TWO_WEEKS:
                return (2 * 7 * 24 * 60 * 60);
            case static::ONE_MONTH:
                return (31 * 24 * 60 * 60);
        }
    }

    /**
     * Get whether the timestamp expired according to the current instance's expiration time.
     *
     * @param int $timestamp
     * @return bool
     */
    public function isExpired(int $timestamp) : bool
    {
        return $timestamp + $this->getSeconds() < time();
    }
}