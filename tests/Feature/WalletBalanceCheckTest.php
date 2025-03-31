<?php

use MarJose123\Pitaka\Exceptions\InsufficientBalanceException;
use function Pest\Laravel\assertDatabaseCount;
use function PHPUnit\Framework\assertEquals;

test('it can check if the wallet balance is enough for the transaction and return false if not enough', function () {
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
    $wallet->deposit(100);

    assertDatabaseCount('wallets', 1);
    assertEquals($user->wallet('peso-wallet')->balance, 100);

    expect($wallet->check(1000))->toBe(false);

});

test('it can check if the wallet balance is enough for the transaction and return true if enough', function () {
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
    $wallet->deposit(100);

    assertDatabaseCount('wallets', 1);
    assertEquals($user->wallet('peso-wallet')->balance, 100);

    expect($wallet->check(50))->toBe(true);

});
