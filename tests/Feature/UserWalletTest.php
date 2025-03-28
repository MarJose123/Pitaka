<?php

use MarJose123\Pitaka\Exceptions\WalletNotFoundException;
use MarJose123\Pitaka\Models\Wallet;
use function PHPUnit\Framework\assertEquals;

test('it can create a wallet for the user through relationship ', function () {
    $user = \Workbench\App\Models\User::factory()->create();

    $wallet = $user->wallets()->create([
        'name' => 'Peso Wallet',
        'slug' => 'peso-wallet',
        'raw_balance' => 0,
        'decimal_places' => 2,
        'metadata' => [
            'Currency' => 'PHP'
        ],
    ]);

    assert($user->wallets()->count() === 1);
    assertEquals($user->wallet('peso-wallet')->id, $wallet->id);
});

test('it can create a wallet for the user through Wallet Model class ', function () {
    $user = \Workbench\App\Models\User::factory()->create();

    $wallet = Wallet::create([
        'owner_id' => $user->id,
        'owner_type' => \Workbench\App\Models\User::class,
        'name' => 'USD Wallet',
        'slug' => 'usd-wallet',
        'raw_balance' => 0,
        'decimal_places' => 2,
        'metadata' => [
            'Currency' => 'PHP'
        ],
    ]);

    assert($user->wallets()->count() === 1);
    assertEquals($user->wallet('usd-wallet')->id, $wallet->id);
});
