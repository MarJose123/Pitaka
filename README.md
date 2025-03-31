# A Simple Virtual Wallet for Laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/marjose123/pitaka.svg?style=flat-square)](https://packagist.org/packages/marjose123/pitaka)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/marjose123/pitaka/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/marjose123/pitaka/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/marjose123/pitaka/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/marjose123/pitaka/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/marjose123/pitaka.svg?style=flat-square)](https://packagist.org/packages/marjose123/pitaka)

"Pitaka" is a term commonly used in the Philippines that translates to "wallet" in English.

Easy creation of Wallets and its transactions.

```PHP

 $wallet = $user->wallets()->create([
        'name' => 'Peso Wallet',
        'slug' => 'peso-wallet',
        'raw_balance' => 0,
        'decimal_places' => 2,
        'metadata' => [
            'Currency' => 'PHP',
        ],
    ]);
    
    
    $wallet->deposit(1000.58);

```

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
 /**
     * --------------------------------------------------------------
     *  Wallet Migration
     * --------------------------------------------------------------
     * This will be used for the Wallet Table.
     */
    'wallet_table' => [
        'default_decimal_places' => 2,
    ],

    /**
     *  -------------------------------------------------------------------
     *  Default User Wallet
     *  -------------------------------------------------------------------
     */
    'user' => [
        'wallet' => null, // Add your default wallet name here. This will be used if you don't provide a wallet name when calling `$user->wallet()`
    ],
];
```


## Usage
You can do a transaction in your wallet by using user `relationship` or through model class.

> [!CAUTION]
> Changing your wallet `decimal_places` value may affect your wallet balance result.

### Wallet balance check against Transaction amount
Check if the wallet balance is enough to do a transaction
```php

$user->wallet('peso-wallet')->check(500) // will return true/false depending on how much amount left in the wallet
```
_or_
```php
$product = Product::find(1);
$user->wallet('peso-wallet')->check($product)
```
Implement `WalletTransaction` contract to your `Product` class.

### Wallet Deposit Transaction
Creating a wallet transaction by depositing an amount. You can also use the `Wallet` model class instead.
```php
 $wallet = $user->wallets()->create([
        'name' => 'Peso Wallet',
        'slug' => 'peso-wallet',
        'raw_balance' => 0,
        'decimal_places' => 2,
        'metadata' => [
            'Currency' => 'PHP',
        ],
    ]);
    
    
    $wallet->deposit(1000.58);
```

### Wallet Pay 
Creating a wallet transaction by paying an item/shop amount using your wallet.
```php
$user->wallet('peso-wallet')->pay(500);
```
_or_
```php
$product = Product::find(1);
$user->wallet('peso-wallet')->pay($product);
```

### Wallet Deduction/Withdraw
You can deduct or do a withdrawal from the wallet with/out fee.
```PHP
 $wallet->withdraw(amount: 900, feeAmount: 100);
```
_or_
```php
  $wallet->withdraw(1000);
```

### Transfer money to other wallet
You can transfer money to other wallets.
```php
$wallet1->transfer(amount: 50, wallet: $wallet2, feeAmount: 50);
```
_or_
```php
$wallet1->transfer(amount: 50, wallet: $wallet2);
```


### Retrieve Wallet Balance

```PHP
$user->wallet('peso-wallet')->balance;
```

Return wallet balance in decimal format.
```php
$user->wallet('peso-wallet')->balance_float;
```

## Testing

```bash
composer package:test
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
