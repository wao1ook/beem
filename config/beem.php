<?php

declare(strict_types=1);

return [
    'api_key' => env('BEEM_SMS_API_KEY', ''),

    'secret_key' => env('BEEM_SMS_SECRET_KEY', ''),

    'sender_name' => env('BEEM_SMS_SENDER_NAME', 'INFO'),

    /*
     * If set to true, the phone addresses will be validated before sending the SMS.
     * This will throw an exception if the phone number is invalid.
     * Set it to false, if you don't want phone addresses validation.
     */
    'validate_phone_addresses' => true,

    /*
     * Beem Sms Sending SMS URL. You can change this if you can use a different URL.
     */
    'sending_sms_url' => 'https://apisms.beem.africa/v1/send',
];
