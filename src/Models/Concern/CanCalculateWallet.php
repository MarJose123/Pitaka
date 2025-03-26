<?php

namespace MarJose123\Pitaka\Models\Concern;

use MarJose123\Pitaka\Contract\WalletTransaction;
use MarJose123\Pitaka\Exceptions\InsufficientBalanceException;
use MarJose123\Pitaka\Models\Wallet;
use ReflectionClass;
use ReflectionException;

trait CanCalculateWallet
{
    public function getIntBalanceAttribute(): int
    {
        if (empty($this->decimal_places) && $this->decimal_places === 0) {
            return $this->balance;
        }

        return (int) $this->balance / pow(10, $this->decimal_places);
    }

    public function getBalanceFloatAttribute(): float
    {
        return (float) number_format($this->balance / pow(10, $this->decimal_places), $this->decimal_places);
    }

    /**
     * Safe check if the balance still enough for creating transactions.
     *
     * If the balance is enough it will return true, else false.
     *
     * @throws ReflectionException
     */
    public function check(float|int|WalletTransaction $transaction): bool
    {
        if ((new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class)) {
            return $this->getBalanceFloatAttribute() > $transaction->getPrice();
        }

        if (is_float($transaction)) {
            return $this->balance >= $this->convertToInt($transaction);
        }

        /** @var int $transaction */
        return $this->balance >= $transaction;
    }

    /**
     * If the Wallet balance is not enough, an exception will be thrown.
     *
     *
     * @throws ReflectionException
     * @throws InsufficientBalanceException
     */
    public function pay(float|int|WalletTransaction $transaction, ?array $metadata): static
    {

        if ((new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class)) {
            $amount = $this->convertToInt($transaction->getPrice());

            if ($amount > $this->balance) {
                throw new InsufficientBalanceException('Insufficient balance');
            }

            $this->decrement('balance', $amount);
            $this->refresh();

            $this->transaction(amount: $amount, metadata: $metadata, transaction: $transaction);

            return $this;
        }

        if (is_float($transaction)) {
            $amount = $this->convertToInt($transaction);
            if ($amount > $this->balance) {
                throw new InsufficientBalanceException('Insufficient balance');
            }

            $this->transaction(amount: $amount, metadata: $metadata);

            $this->decrement('balance', $amount);
            $this->refresh();

            return $this;
        }

        /** @var int $transaction */
        if ($transaction > $this->balance) {
            throw new InsufficientBalanceException('Insufficient balance');
        }

        $this->transaction(amount: $transaction, metadata: $metadata);

        $this->decrement('balance', $transaction);
        $this->refresh();

        return $this;

    }

    /**
     * @throws ReflectionException
     */
    private function transaction(float|int|WalletTransaction $amount, array $metadata, ?WalletTransaction $transaction = null): void
    {
        $this->transactions()->create([
            'amount' => $amount,
            ...['transaction_id' => (new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class) ? (new ReflectionClass($transaction))->getMethod('getKey')->invoke($transaction) : []],
            ...['transaction_type' => (new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class) ? get_class($transaction) : []],
            'running_balance' => $this->balance,
            'metadata' => [
                ...$metadata,
                'decimal_places' => $this->decimal_places,
            ],
        ]);
    }

    /**
     * Convert Float amount to whole number
     */
    private function convertToInt(float $amount): int
    {
        return (int) $amount * pow(10, $this->decimal_places);
    }
}
