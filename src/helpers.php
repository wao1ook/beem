<?php

declare(strict_types=1);

use Emanate\BeemSms\BeemSms;
use Emanate\BeemSms\MulticountrySms;
use Emanate\BeemSms\Otp;

if ( ! function_exists('beem')) {
    function beem(): BeemSms
    {
        return app('beem-sms');
    }
}

if ( ! function_exists('beem_multicountry')) {
    function beem_multicountry(): MulticountrySms
    {
        return app('beem-multicountry-sms');
    }
}

if ( ! function_exists('beem_otp')) {
    function beem_otp(): Otp
    {
        return app('beem-otp');
    }
}
