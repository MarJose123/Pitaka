<?php

use MarJose123\Pitaka\Exceptions\InsufficientBalanceException;

use function Pest\Laravel\assertDatabaseCount;
use function PHPUnit\Framework\assertEquals;

test('it can withdraw or deduct to the wallet', function () {
    $user = \Workbench\App\Models\User::factory()->create();
    $wallet = $user->wallets()->create([
        'name' => 'Peso Wallet',
        'slug' => 'peso-wallet',
        'raw_balance' => 0,
        'decimal_places' => 2,
        'metadata' => [
            'Currency' => 'PHP',
        ],
    ]);

    // Add money to the wallet
    $wallet->deposit(1000);

    assertDatabaseCount('wallets', 1);
    assertEquals($user->wallet('peso-wallet')->balance, 1000);

    $wallet->withdraw(1000);
    assertEquals($user->wallet('peso-wallet')->balance, 0);
});
test('it can withdraw or deduct to the wallet with fees', function () {
    $user = \Workbench\App\Models\User::factory()->create();
    $wallet = $user->wallets()->create([
        'name' => 'Peso Wallet',
        'slug' => 'peso-wallet',
        'raw_balance' => 0,
        'decimal_places' => 2,
        'metadata' => [
            'Currency' => 'PHP',
        ],
    ]);

    // Add money to the wallet
    $wallet->deposit(1000);

    assertDatabaseCount('wallets', 1);
    assertEquals($user->wallet('peso-wallet')->balance, 1000);

    $wallet->withdraw(amount: 900, feeAmount: 100);
    assertEquals($user->wallet('peso-wallet')->balance, 0);
});

test('it can withdraw or deduct to the wallet with fees and throw exception class for insufficient balance', function () {
    $user = \Workbench\App\Models\User::factory()->create();
    $wallet = $user->wallets()->create([
        'name' => 'Peso Wallet',
        'slug' => 'peso-wallet',
        'raw_balance' => 0,
        'decimal_places' => 2,
        'metadata' => [
            'Currency' => 'PHP',
        ],
    ]);

    // Add money to the wallet
    $wallet->deposit(1000);

    assertDatabaseCount('wallets', 1);
    assertEquals($user->wallet('peso-wallet')->balance, 1000);

    expect(fn () => $wallet->withdraw(amount: 900, feeAmount: 150))->toThrow(InsufficientBalanceException::class);
});

test('it can withdraw or deduct to the wallet and throw an exception if wallet balance is not enough', function () {
    $user = \Workbench\App\Models\User::factory()->create();
    $wallet = $user->wallets()->create([
        'name' => 'Peso Wallet',
        'slug' => 'peso-wallet',
        'raw_balance' => 0,
        'decimal_places' => 2,
        'metadata' => [
            'Currency' => 'PHP',
        ],
    ]);

    // Add money to the wallet
    $wallet->deposit(1000);

    assertDatabaseCount('wallets', 1);
    assertEquals($user->wallet('peso-wallet')->balance, 1000);

    expect(fn () => $wallet->withdraw(amount: 1000, feeAmount: 50))->toThrow(InsufficientBalanceException::class);
});
