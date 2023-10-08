<?php

declare(strict_types=1);

return [
    'api_key' => env('BEEM_SMS_API_KEY', ''),

    'secret_key' => env('BEEM_SMS_SECRET_KEY', ''),

    'sender_name' => env('BEEM_SMS_SENDER_NAME', 'INFO'),

    'debug' => true,

    'validate_phone_addresses' => true,
];
