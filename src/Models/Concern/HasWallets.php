<?php

namespace MarJose123\Pitaka\Models\Concern;

use BackedEnum;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use MarJose123\Pitaka\Models\Wallet;

trait HasWallets
{
    /**
     * @throws \ReflectionException
     */
    public function wallet(string|BackedEnum|null $walletName = null): Wallet
    {
        if (is_string($walletName)) {
            return $this->wallets()->whereName($walletName)->firstOrFail();
        }

        if ((new \ReflectionClass($walletName))->isEnum()) {
            return $this->wallets()->whereName($walletName->value)->firstOrFail();
        }

        if ($walletName === null) {
            if (config('pitaka.user.wallet') !== null) {
                return $this->wallets()->whereName(config('pitaka.user.wallet'))->firstOrFail();
            }

            return $this->wallets()->firstOrFail();
        }

    }

    public function wallets(): MorphMany
    {
        return $this->morphMany(Wallet::class, 'owner')->chaperone();
    }
}
