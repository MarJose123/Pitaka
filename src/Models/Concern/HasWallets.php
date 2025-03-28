<?php

namespace MarJose123\Pitaka\Models\Concern;

use BackedEnum;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use MarJose123\Pitaka\Exceptions\WalletNotFoundException;
use MarJose123\Pitaka\Models\Wallet;
use ReflectionException;
use Str;

trait HasWallets
{
    /**
     * @param string|BackedEnum|null $walletName Wallet Slug Name
     * @return Wallet
     * @throws ReflectionException
     * @throws WalletNotFoundException
     */
    public function wallet(string|BackedEnum|null $walletName = null): Wallet
    {
        if (is_string($walletName)) {
            return $this->wallets()->whereSlug(Str::slug($walletName))->firstOrFail();
        }

        if ((new \ReflectionClass($walletName))->isEnum()) {
            return $this->wallets()->whereSlug(Str::slug($walletName->value))->firstOrFail();
        }

        if ($walletName === null) {
            if (config('pitaka.user.wallet') !== null) {
                return $this->wallets()->whereSlug(Str::slug(config('pitaka.user.wallet')))->firstOrFail();
            }

            return $this->wallets()->firstOrFail();
        }

        throw new WalletNotFoundException('Default wallet cannot be found.');
    }

    public function wallets(): MorphMany
    {
        return $this->morphMany(Wallet::class, 'owner')->chaperone();
    }
}
