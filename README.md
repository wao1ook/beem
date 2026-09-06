<p align="center"><img src="https://beem.africa/wp-content/uploads/2020/12/Beem-menu-logo-02.svg" width="400"></p>

# Beem Africa SMS Package for Laravel Applications
[![Latest Stable Version](https://poser.pugx.org/emanate/beem/v)](https://packagist.org/packages/emanate/beem)
[![Total Downloads](https://poser.pugx.org/emanate/beem/downloads)](https://packagist.org/packages/emanate/beem)
[![Monthly Downloads](https://poser.pugx.org/emanate/beem/d/monthly)](https://packagist.org/packages/emanate/beem)
[![License](https://poser.pugx.org/emanate/beem/license)](https://packagist.org/packages/emanate/beem) 

## Installation

Install the package via composer:

```bash
composer require emanate/beem
```

Publish the config file using:

```bash
php artisan vendor:publish --tag="beem"
```

These are the contents of the published config file:

```php
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
    *   Path to the class that handles the Phone Address Validation. Ensure correct mapping of your custom validator class by updating 
    *   The 'validator_class' configuration to point to the appropriate namespace and class name.
    *   Please make sure the custom validator class implements the namespace Emanate\BeemSms\Contracts\Validator interface
    */
    'validator_class' => \Emanate\BeemSms\DefaultValidator::class,

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
```

> It is crucial to double-check and ensure that your config file is kept up to date with the latest settings and configurations.

## Usage

Sending SMS using a Facade
```php
use Emanate\BeemSms\Facades\BeemSms;


BeemSms::content('Your message here')->loadRecipients(User::all())->send();
```
or a helper

```php
beem()->content('Your message here')->loadRecipients(User::all())->send();
```

Suppose you are using a different name for your column or property for phone numbers on your model or collection while using the loadRecipients() method. In that case, you should explicitly specify it on the method. By default, 'phone_number' is used.

```php
use Emanate\BeemSms\Facades\BeemSms;


BeemSms::content('Your message here')->loadRecipients(User::all(), 'column_name')->send();
```

Instead of passing a collection of phone numbers, you could pass a single phone number in an array or an array of phone numbers.

```php
use Emanate\BeemSms\Facades\BeemSms;


BeemSms::content('Your message here')->getRecipients(array('255700000000', '255711111111', '255722222222'))->send();
```

You have a list of phone numbers and it's not a collection or an array, you can unpack them using the unpackRecipients() method.

```php
use Emanate\BeemSms\Facades\BeemSms;


BeemSms::content('Your message here')->unpackRecipients('255700000000', '255711111111', '255722222222')->send();

```

You can use custom credentials ( API and Secret Key) on runtime, whenever it suits your needs. Using these methods do not recuse you from the responsibility of adding your credentials to wherever you store your secret environment variables. Please make sure you have your keys registered in the config before you start using the package.

```php
use Emanate\BeemSms\Facades\BeemSms;


BeemSms::content('Your message here')
->loadRecipients(User::all(), 'column_name')
->apiKey('your custom api key')
->secretKey('your custom secret key')
->send();
```

### Checking Balance

To check your Beem SMS balance, you can use the `balance` method provided by the `BeemSms` facade. Here's an example:

```php
use Emanate\BeemSms\Facades\BeemSms;

BeemSms::balance();
```

### Overriding the sender name

The sender name defaults to the `sender_name` config value, but it can be overridden per message. Beem accepts a maximum of 11 alphanumeric characters, and the sender ID must already be registered and active on your account.

```php
BeemSms::content('Your message here')
    ->senderName('SHOP')
    ->getRecipients(['255700000000'])
    ->send();
```

### Encoding

By default messages are sent with GSM encoding (`encoding = 0`). If your message contains characters outside the GSM alphabet, send it as unicode instead:

```php
BeemSms::content('Ujumbe wako hapa 😀')
    ->unicode()
    ->getRecipients(['255700000000'])
    ->send();

// or set the raw value
BeemSms::content('Your message here')
    ->encoding(\Emanate\BeemSms\BeemSms::ENCODING_UNICODE)
    ->getRecipients(['255700000000'])
    ->send();
```

The default can also be changed globally through the `encoding` config option.

### Scheduling a message

Beem accepts an optional `schedule_time` in **GMT+0**, formatted as `Y-m-d H:i`. You may pass either a string in that format or any `DateTimeInterface`, which will be converted to UTC for you.

```php
BeemSms::content('Your message here')
    ->getRecipients(['255700000000'])
    ->scheduleTime(now()->addHours(2))
    ->send();

BeemSms::content('Your message here')
    ->getRecipients(['255700000000'])
    ->scheduleTime('2026-01-01 09:00')
    ->send();
```

### Job name and campaign title

Both fields are optional and are used for tracking in the Beem dashboard.

```php
BeemSms::content('Your message here')
    ->getRecipients(['255700000000'])
    ->jobName('welcome-emails')
    ->campaignTitle('January onboarding')
    ->send();
```

### Reading the send response

`send()` returns the raw PSR-7 response. If you would rather work with the decoded body — and have a non-success Beem response code raised as an exception — use `sendAndParse()`:

```php
use Emanate\BeemSms\Exceptions\BeemApiException;

try {
    $response = BeemSms::content('Your message here')
        ->getRecipients(['255700000000'])
        ->sendAndParse();

    // $response['request_id'], $response['valid'], $response['invalid'], ...
} catch (BeemApiException $e) {
    // $e->getCode() is the Beem response code, e.g. 102 for an insufficient balance
    report($e->description());
}
```

### Delivery reports

Beem exposes delivery status per recipient, keyed by the `request_id` returned from a send. Wait at least five minutes after submitting before checking, so the mobile operator has time to report back.

```php
$response = BeemSms::content('Your message here')
    ->getRecipients(['255700000002'])
    ->sendAndParse();

$report = BeemSms::deliveryReport('255700000002', $response['request_id']);

// [['dest_addr' => '255700000002', 'status' => 'DELIVERED', 'request_id' => '31951']]
```

The `status` is one of `PENDING`, `DELIVERED` or `UNDELIVERED`.

### Sender names

List the sender IDs registered on your account, along with their approval status:

```php
BeemSms::senderNames();

BeemSms::senderNames(['page' => 1, 'limit' => 20, 'status' => 'active', 'q' => 'SHOP']);
```

Supported filters are `page`, `limit`, `sortBy`, `sortOrder`, `q` and `status`.

### SMS templates

```php
// List, with optional page, limit, sortBy and sortOrder filters
BeemSms::templates(['page' => 1, 'limit' => 20]);

// Create
$template = BeemSms::createTemplate('Welcome', 'Welcome to our service!');

// Update
BeemSms::updateTemplate($template['data']['id'], 'Welcome', 'Welcome aboard!');

// Delete
BeemSms::deleteTemplate($template['data']['id']);
```

### Error handling

Every method that returns a decoded array (`balance`, `deliveryReport`, `senderNames`, `templates`, `createTemplate`, `updateTemplate`, `deleteTemplate`, `sendAndParse`) throws an `Emanate\BeemSms\Exceptions\BeemApiException` when Beem answers with an error. The exception code is Beem's own response code, and `BeemApiException::RESPONSE_CODES` maps every documented code to its meaning.

### Validation
Sometimes phone addresses are not exactly in the format that works for Beem, then the whole operation of sending messages to recipients fails. If you need to validate phone addresses, you need to leave the option **`validate_phone_addresses`** in the config to `true`. This library comes with a default validator that will handle some use-cases. In the occurrence that you need to use your own validator, you can do so by providing the path to your custom class on the **`validator_class`** option that you can find in the config. 

> Please make sure that your custom Validator class implements the **`Emanate\BeemSms\Contracts\Validator`** interface.

## Testing
You can run the tests with:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Emanate Software](https://github.com/wao1ook)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
