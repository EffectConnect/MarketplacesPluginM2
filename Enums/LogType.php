<?php

namespace EffectConnect\Marketplaces\Enums;

/**
 * Class LogType
 * @package EffectConnect\Marketplaces\Enums
 * @method static LogType SUCCESS()
 * @method static LogType INFO()
 * @method static LogType NOTICE()
 * @method static LogType WARNING()
 * @method static LogType ERROR()
 * @method static LogType FATAL()
 */
class LogType extends AbstractEnum
{
    /**
     * Success log type.
     */
    const SUCCESS   = 'success';

    /**
     * Info log type.
     */
    const INFO      = 'info';

    /**
     * Notice log type.
     */
    const NOTICE    = 'notice';

    /**
     * Warning log type.
     */
    const WARNING   = 'warning';

    /**
     * Error log type.
     */
    const ERROR     = 'error';

    /**
     * Fatal log type.
     */
    const FATAL  = 'fatal';

    /**
     * Get the grid column cell CSS class.
     *
     * @return string
     */
    public function getCssClass() : string
    {
        switch ($this->getValue()) {
            case static::SUCCESS:
            case static::INFO:
            case static::NOTICE:
            default:
                return 'grid-severity-notice';
            case static::WARNING:
                return 'grid-severity-minor';
            case static::ERROR:
                return 'grid-severity-major';
            case static::FATAL:
                return 'grid-severity-critical';
        }
    }

    /**
     * Get index column cell HTML.
     *
     * @return string
     */
    public function getIndexColumnCell() : string
    {
        return '
            <span class="' . $this->getCssClass() . '">
                <span>' . $this->getLabel() . '</span>
            </span>
        ';
    }
}