<?php

use MarJose123\Pitaka\Exceptions\InsufficientBalanceException;

use function Pest\Laravel\assertDatabaseCount;
use function PHPUnit\Framework\assertEquals;

test('it can pay using wallet balance', function () {
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

    // Pay using wallet
    $wallet->pay(500);
    assertEquals($user->wallet('peso-wallet')->balance, 500);
});
test('it will throw an error if wallet is insufficient balance', function () {
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

    expect(fn () => $wallet->pay(2000))->toThrow(InsufficientBalanceException::class);
});
