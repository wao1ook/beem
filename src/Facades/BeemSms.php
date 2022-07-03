<?php

namespace Emanate\BeemSms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Emanate\BeemSms\BeemSms
 */
class BeemSms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'beem-sms';
    }
}
