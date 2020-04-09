<?php

namespace EffectConnect\Marketplaces\Enums\Api;

use MyCLabs\Enum\Enum;

/**
 * Class IdentifierType
 * @package EffectConnect\Marketplaces\Enums\Api
 * @method static IdentifierType CONNECTION_IDENTIFIER()
 * @method static IdentifierType CONNECTION_INVOICE()
 * @method static IdentifierType CONNECTION_NUMBER()
 * @method static IdentifierType EFFECTCONNECT_NUMBER()
 * @method static IdentifierType CHANNEL_IDENTIFIER()
 * @method static IdentifierType CHANNEL_NUMBER()
 */
class IdentifierType extends Enum
{
    /**
     * Connection identifier.
     */
    const CONNECTION_IDENTIFIER = 'connectionIdentifier';

    /**
     * Connection invoice.
     */
    const CONNECTION_INVOICE    = 'connectionInvoice';

    /**
     * Connection number.
     */
    const CONNECTION_NUMBER     = 'connectionNumber';

    /**
     * EffectConnect number.
     */
    const EFFECTCONNECT_NUMBER  = 'effectConnectNumber';

    /**
     * Channel identifier.
     */
    const CHANNEL_IDENTIFIER    = 'channelIdentifier';

    /**
     * Channel number.
     */
    const CHANNEL_NUMBER        = 'channelNumber';
}