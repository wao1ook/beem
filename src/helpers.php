<?php

declare(strict_types=1);

use Emanate\BeemSms\BeemSms;

if ( ! function_exists('beem')) {
    function beem(): BeemSms
    {
        return app('beem-sms');
    }
}
