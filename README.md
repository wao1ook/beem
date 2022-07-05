# Refactoring way of sending SMS

[![Latest Version on Packagist](https://img.shields.io/packagist/v/emanate/beem.svg?style=flat-square)](https://packagist.org/packages/emanate/beem)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/emanate/beem/run-tests?label=tests)](https://github.com/emanate/beem/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/emanate/beem/Check%20&%20fix%20styling?label=code%20style)](https://github.com/emanate/beem/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/emanate/beem.svg?style=flat-square)](https://packagist.org/packages/emanate/beem)

## Installation

You can install the package via composer:

```bash
composer require emanate/beem
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="beem-config"
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

```
Coming Soon
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
