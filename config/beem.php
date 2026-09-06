<?php

declare(strict_types=1);

return [
    'api_key' => env('BEEM_SMS_API_KEY', ''),

    'secret_key' => env('BEEM_SMS_SECRET_KEY', ''),

    'sender_name' => env('BEEM_SMS_SENDER_NAME', 'INFO'),

    /*
     * Optional access token. When set, it is sent in the Authorization header
     * (without a Bearer prefix) instead of the API key and secret.
     */
    'access_token' => env('BEEM_ACCESS_TOKEN'),

    /*
     * If set to true, the phone addresses will be validated before sending the SMS.
     * This will throw an exception if the phone number is invalid.
     * Set it to false, if you don't want phone addresses validation.
     */
    'validate_phone_addresses' => true,

    /*
    *   Path to the class that handles the Phone Address Validation. Ensure correct mapping of your custom validator class by updating
    *   the 'validator_class' configuration to point to the appropriate namespace and class name.
    *   Please make sure the custom validator class implements the Emanate\BeemSms\Contracts\Validator interface
    */
    'validator_class' => Emanate\BeemSms\DefaultValidator::class,

    /*
     * Beem Sms Sending SMS URL. You can change this if you can use a different URL.
     * Staging is available at https://dev-sms.beem.africa/v1
     */
    'sending_sms_url' => env('BEEM_SMS_SENDING_URL', 'https://apisms.beem.africa/v1'),

    /*
     * Base URL for the public Beem SMS endpoints: vendor balance, sender names and SMS templates.
     */
    'public_api_url' => env('BEEM_SMS_PUBLIC_API_URL', 'https://apisms.beem.africa/public/v1'),

    /*
     * Base URL for the Beem delivery reports API.
     */
    'delivery_reports_url' => env('BEEM_SMS_DELIVERY_REPORTS_URL', 'https://dlrapi.beem.africa/public/v1'),

    /*
     * Default message encoding sent as the `encoding` field. 0 is the default GSM encoding.
     * Use \Emanate\BeemSms\BeemSms::ENCODING_UNICODE for messages containing non-GSM characters.
     */
    'encoding' => env('BEEM_SMS_ENCODING', 0),

    /*
     * Verify the TLS certificate of the Beem API. Keep this true in production.
     * It may be set to a path to a CA bundle, or false to disable verification entirely.
     */
    'verify_ssl' => env('BEEM_SMS_VERIFY_SSL', true),

    /*
     * Guzzle request timeout, in seconds.
     */
    'timeout' => env('BEEM_SMS_TIMEOUT', 30),
];
