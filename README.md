<p align="center"><img src="https://beem.africa/wp-content/uploads/2020/12/Beem-menu-logo-02.svg" width="400"></p>

# Beem Africa SMS Package for Laravel Applications
[![Latest Stable Version](http://poser.pugx.org/emanate/beem/v)](https://packagist.org/packages/emanate/beem)
[![Total Downloads](http://poser.pugx.org/emanate/beem/downloads)](https://packagist.org/packages/emanate/beem) 
[![License](http://poser.pugx.org/emanate/beem/license)](https://packagist.org/packages/emanate/beem) 

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
    *   the 'validator_class' configuration to point to the appropriate namespace and class name.
    *   Please make sure the custom validator class implements the namespace Emanate\BeemSms\Contracts\Validator interface
    */
    'validator_class' => \Emanate\BeemSms\DefaultValidator::class,

    /*
     * Beem Sms Sending SMS URL. You can change this if you can use a different URL.
     */
    'sending_sms_url' => 'https://apisms.beem.africa/v1/send',
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

If you are using a different name for your column or property for phone numbers on your model or collection while using the loadRecipients() method, you should explicitly specify it on the method. By default, 'phone_number' is used.

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
