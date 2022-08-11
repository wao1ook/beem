<?php

return [
    'api_key' => env('BEEM_SMS_API_KEY', ''),

    'secret_key' => env('BEEM_SMS_SECRET_KEY', ''),

    'sender_name' => env('BEEM_SMS_SENDER_NAME', 'INFO'),

    'api_url' => env('BEEM_SMS_API_URL', 'https://apisms.beem.africa/v1/send'),

    'debug' => true,
];
