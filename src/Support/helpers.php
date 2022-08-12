<?php

if (!function_exists('beem')) {
    function beem($key = null, $default = null)
    {
        return app('beem-sms');
    }
}
