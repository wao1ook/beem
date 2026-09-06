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

    /*
     * Beem Two-Way SMS. Beem posts mobile-originated messages to a callback URL,
     * which you register in the Beem dashboard. Set 'enabled' to true to have this
     * package register the route, then listen for the InboundSmsReceived event.
     */
    'two_way' => [
        'enabled' => env('BEEM_TWO_WAY_ENABLED', false),

        /*
         * Path the callback route is registered at. Give Beem the full URL to it.
         */
        'path' => env('BEEM_TWO_WAY_PATH', 'beem/inbound'),

        /*
         * Middleware applied to the callback route. The Beem credential check is
         * always applied on top of whatever is listed here.
         */
        'middleware' => ['api'],

        /*
         * Authenticate every inbound callback before processing it. Only turn this
         * off if you are authenticating the callback somewhere else.
         */
        'verify_credentials' => true,

        /*
         * Optional dedicated token for the callback, checked against the Authorization
         * header. When empty, the access token and then the API key and secret are used.
         */
        'token' => env('BEEM_TWO_WAY_TOKEN'),
    ],

    /*
     * Beem OTP. Shares the API key and secret above, but lives on its own host.
     * The application ID comes from the OTP application you create in the Beem dashboard.
     */
    'otp' => [
        'app_id' => env('BEEM_OTP_APP_ID'),

        'url' => env('BEEM_OTP_URL', 'https://apiotp.beem.africa/v1'),
    ],

    /*
     * Beem Multicountry SMS. This is a separate platform from the Beem SMS API above:
     * it has its own host and authenticates with a username and password.
     */
    'multicountry' => [
        'username' => env('BEEM_MULTICOUNTRY_USERNAME', ''),

        'password' => env('BEEM_MULTICOUNTRY_PASSWORD', ''),

        /*
         * Default sender address. May be overridden per message with from().
         */
        'source_address' => env('BEEM_MULTICOUNTRY_SOURCE_ADDRESS'),

        'send_url' => env('BEEM_MULTICOUNTRY_SEND_URL', 'https://api.blsmsgw.com:8443/bin/send.json'),

        'balance_url' => env(
            'BEEM_MULTICOUNTRY_BALANCE_URL',
            'https://www.blsmsgw.com/portal/api/userAccountBalance'
        ),
    ],
];
