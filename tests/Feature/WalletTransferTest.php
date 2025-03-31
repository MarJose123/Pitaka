<?php

use function Pest\Laravel\assertDatabaseCount;
use function PHPUnit\Framework\assertEquals;

test('it can transfer a balance amount to other wallet without fees', function () {
    $user = \Workbench\App\Models\User::factory()->create();
    $wallet1 = $user->wallets()->create([
        'name' => 'Peso Wallet',
        'slug' => 'peso-wallet',
        'raw_balance' => 0,
        'decimal_places' => 2,
        'metadata' => [
            'Currency' => 'PHP',
        ],
    ]);
    $wallet2 = $user->wallets()->create([
        'name' => 'Peso Saving Wallet',
        'slug' => 'peso-saving-wallet',
        'raw_balance' => 0,
        'decimal_places' => 2,
        'metadata' => [
            'Currency' => 'PHP',
        ],
    ]);

    // Add money to the wallet
    $wallet1->deposit(100);
    assertDatabaseCount('wallets', 2);

    assertEquals($wallet1->balance, 100);
    assertEquals($wallet2->balance, 0);

    $wallet1->transfer(amount: 50, wallet: $wallet2);

    assertEquals($wallet1->balance, 50);
    assertEquals($wallet2->balance, 50);

});

test('it can transfer a balance amount to other wallet with fees', function () {
    $user = \Workbench\App\Models\User::factory()->create();
    $wallet1 = $user->wallets()->create([
        'name' => 'Peso Wallet',
        'slug' => 'peso-wallet',
        'raw_balance' => 0,
        'decimal_places' => 2,
        'metadata' => [
            'Currency' => 'PHP',
        ],
    ]);
    $wallet2 = $user->wallets()->create([
        'name' => 'Peso Saving Wallet',
        'slug' => 'peso-saving-wallet',
        'raw_balance' => 0,
        'decimal_places' => 2,
        'metadata' => [
            'Currency' => 'PHP',
        ],
    ]);

    // Add money to the wallet
    $wallet1->deposit(100);
    assertDatabaseCount('wallets', 2);

    assertEquals($wallet1->balance, 100);
    assertEquals($wallet2->balance, 0);

    $wallet1->transfer(amount: 50, wallet: $wallet2, feeAmount: 50);

    assertEquals($wallet1->balance, 0);
    assertEquals($wallet2->balance, 50);

});
