<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Facades;

use Illuminate\Support\Facades\Facade;

final class BeemSms extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @method static content()
     * @method static getRecipients()
     * @method static loadRecipients()
     * @method static unpackRecipients()
     */
    protected static function getFacadeAccessor(): string
    {
        return 'beem-sms';
    }
}
