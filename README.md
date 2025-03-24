# A Simple Virtual Wallet for Laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/marjose123/pitaka.svg?style=flat-square)](https://packagist.org/packages/marjose123/pitaka)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/marjose123/pitaka/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/marjose123/pitaka/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/marjose123/pitaka/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/marjose123/pitaka/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/marjose123/pitaka.svg?style=flat-square)](https://packagist.org/packages/marjose123/pitaka)

"Pitaka" is a term commonly used in the Philippines that translates to "wallet" in English.


## Installation

You can install the package via composer:

```bash
composer require marjose123/pitaka
composer dump-autoload
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="pitaka-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="pitaka-config"
```

This is the contents of the published config file:

```php
return [
];
```


## Usage

```php
$pitaka = new MarJose123\Pitaka();
echo $pitaka->echoPhrase('Hello, MarJose123!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [MarJose123](https://github.com/MarJose123)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
