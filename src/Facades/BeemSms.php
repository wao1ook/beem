<?php

namespace Emanate\BeemSms\Facades;

use Illuminate\Support\Facades\Facade;

class BeemSms extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'beem-sms';
    }
}
