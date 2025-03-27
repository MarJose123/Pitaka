<?php

namespace MarJose123\Pitaka\Models\Concern;

use MarJose123\Pitaka\Contract\WalletTransaction;
use MarJose123\Pitaka\Enums\TransactionTypeEnum;
use MarJose123\Pitaka\Exceptions\InsufficientBalanceException;
use ReflectionClass;
use ReflectionException;

trait CanCalculateWallet
{
    public function getBalanceAttribute(): int|float
    {
        if (empty($this->decimal_places) && $this->decimal_places === 0) {
            return $this->raw_balance;
        }

        return (int) $this->raw_balance / pow(10, $this->decimal_places);
    }

    public function getBalanceFloatAttribute(): float
    {
        return $this->convertToDecimal($this->raw_balance);
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
            return $this->getBalanceFloatAttribute() > $transaction->getAmount();
        }

        if (is_float($transaction)) {
            return $this->raw_balance >= $this->convertToInt($transaction);
        }

        /** @var int $transaction */
        return $this->raw_balance >= $transaction;
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
            $amount = $this->convertToInt($transaction->getAmount());

            if ($amount > $this->raw_balance) {
                throw new InsufficientBalanceException('Insufficient balance');
            }

            $this->decrement('raw_balance', $amount);
            $this->refresh();

            $this->transaction(amount: $amount, metadata: $metadata, transaction: $transaction);

            return $this;
        }

        if (is_float($transaction)) {
            $amount = $this->convertToInt($transaction);
            if ($amount > $this->raw_balance) {
                throw new InsufficientBalanceException('Insufficient balance');
            }

            $this->transaction(amount: $amount, metadata: $metadata);

            $this->decrement('raw_balance', $amount);
            $this->refresh();

            return $this;
        }

        /** @var int $transaction */
        if ($transaction > $this->raw_balance) {
            throw new InsufficientBalanceException('Insufficient balance');
        }

        $this->transaction(amount: $transaction, metadata: $metadata);

        $this->decrement('raw_balance', $transaction);
        $this->refresh();

        return $this;

    }

    /**
     * This will add an amount to the current wallet balance.
     *
     * @return $this
     *
     * @throws ReflectionException
     */
    public function deposit(float|int|WalletTransaction $transaction, ?array $metadata): static
    {
        if ((new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class)) {
            $amount = $this->convertToInt($transaction->getAmount());
            $this->increment('raw_balance', $amount);
            $this->refresh();

            $this->transaction(amount: $amount, metadata: $metadata, transaction: $transaction, transactionType: TransactionTypeEnum::DEPOSIT);

            return $this;
        }
        if (is_float($transaction)) {
            $amount = $this->convertToInt($transaction);
            $this->increment('raw_balance', $amount);
            $this->refresh();
            $this->transaction(amount: $amount, metadata: $metadata, transactionType: TransactionTypeEnum::DEPOSIT);

            return $this;
        }

        $this->transaction(amount: $transaction, metadata: $metadata, transactionType: TransactionTypeEnum::DEPOSIT);
        $this->increment('raw_balance', $transaction);
        $this->refresh();

        return $this;

    }

    /**
     * @throws InsufficientBalanceException
     * @throws ReflectionException
     */
    public function fee(float|int|WalletTransaction $transaction, ?array $metadata): static
    {
        if ((new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class)) {
            $amount = $this->convertToInt($transaction->getAmount());
            if ($amount > $this->raw_balance) {
                throw new InsufficientBalanceException('Insufficient balance');
            }
            $this->decrement('raw_balance', $amount);
            $this->refresh();
            $this->transaction(amount: $amount, metadata: $metadata, transaction: $transaction, transactionType: TransactionTypeEnum::FEE);

            return $this;
        }

        if (is_float($transaction)) {
            $amount = $this->convertToInt($transaction);
            if ($amount > $this->raw_balance) {
                throw new InsufficientBalanceException('Insufficient balance');
            }
            $this->transaction(amount: $amount, metadata: $metadata, transactionType: TransactionTypeEnum::FEE);
            $this->decrement('raw_balance', $amount);
            $this->refresh();

            return $this;
        }

        /** @var int $transaction */
        if ($transaction > $this->raw_balance) {
            throw new InsufficientBalanceException('Insufficient balance');
        }

        $this->transaction(amount: $transaction, metadata: $metadata, transactionType: TransactionTypeEnum::FEE);
        $this->decrement('raw_balance', $transaction);
        $this->refresh();

        return $this;

    }

    /**
     * Record the transaction happens in the wallet.
     *
     * @throws ReflectionException
     */
    private function transaction(float|int|WalletTransaction $amount, ?array $metadata, ?WalletTransaction $transaction = null, ?TransactionTypeEnum $transactionType = TransactionTypeEnum::PAYMENT): void
    {
        $this->transactions()->create([
            'amount' => $amount,
            ...['transaction_id' => (new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class) ? (new ReflectionClass($transaction))->getMethod('getKey')->invoke($transaction) : []],
            ...['transaction_type' => (new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class) ? get_class($transaction) : []],
            'running_balance' => $this->raw_balance,
            'metadata' => [
                ...($metadata ?? []),
                'transaction_type' => (new ReflectionClass($transactionType))->isEnum() ? $transactionType->value : $transactionType,
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

    private function convertToDecimal(int $amount): float
    {
        return (float) number_format($amount / pow(10, $this->decimal_places), $this->decimal_places);
    }
}
