<p align="center"><img src="https://beem.africa/wp-content/uploads/2020/12/Beem-menu-logo-02.svg" width="400"></p>

# Beem Africa SMS Package for Laravel Applications
[![Latest Stable Version](http://poser.pugx.org/emanate/beem/v)](https://packagist.org/packages/emanate/beem)
[![Total Downloads](http://poser.pugx.org/emanate/beem/downloads)](https://packagist.org/packages/emanate/beem) 
[![License](http://poser.pugx.org/emanate/beem/license)](https://packagist.org/packages/emanate/beem) 

## Installation

You can install the package via composer:

```bash
composer require emanate/beem
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="beem"
```

This is the contents of the published config file:

```php
return [
    'api_key' => env('BEEM_SMS_API_KEY', ''),

    'secret_key' => env('BEEM_SMS_SECRET_KEY', ''),

    'sender_name' => env('BEEM_SMS_SENDER_NAME', 'INFO'),

    'api_url' => env('BEEM_SMS_API_URL', 'https://apisms.beem.africa/v1/send'),
];
```

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

If you plan on generating your phone number addresses, you could use the getRecipients() method. Be advised, that the getRecipients() method will receive variables in an array format.

```php
use Emanate\BeemSms\Facades\BeemSms;


BeemSms::content('Your message here')->getRecipients(array('255700000000', '255711111111', '255722222222'))->send();
```

## Testing

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
