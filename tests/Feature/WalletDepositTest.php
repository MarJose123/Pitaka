<?php

use function Pest\Laravel\assertDatabaseCount;
use function PHPUnit\Framework\assertEquals;

test('it can deposit to a wallet in decimal', function () {
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

    $user->wallet('peso-wallet')->deposit(1000.58);

    assertDatabaseCount('wallets', 1);
    assertEquals($user->wallet('peso-wallet')->balance_float, 1000.58);
});

test('it can deposit to a wallet in decimal from class return', function () {
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

    $wallet->deposit(1000.58);

    assertDatabaseCount('wallets', 1);
    assertEquals($user->wallet('peso-wallet')->balance_float, 1000.58);
    assertEquals($user->id, $wallet->owner->id);
});

test('it can deposit to a wallet in integer', function () {
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

    $user->wallet('peso-wallet')->deposit(1000);

    assertDatabaseCount('wallets', 1);
    assertEquals($user->wallet('peso-wallet')->balance, 1000);
});
test('it can deposit to a wallet in integer from class return', function () {
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

    $wallet->deposit(1000);

    assertDatabaseCount('wallets', 1);
    assertEquals($user->wallet('peso-wallet')->balance, 1000);
    assertEquals($user->id, $wallet->owner->id);
});
