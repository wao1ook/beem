<?php

namespace Emanate\BeemSms\Facades;

class BeemSms
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'beem-sms';
    }
}
