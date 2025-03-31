<?php

namespace MarJose123\Pitaka\Models\Concern;

use Exception;
use MarJose123\Pitaka\Contract\WalletTransaction;
use MarJose123\Pitaka\Enums\TransactionTypeEnum;
use MarJose123\Pitaka\Exceptions\InsufficientBalanceException;
use MarJose123\Pitaka\Exceptions\WalletTransferException;
use MarJose123\Pitaka\Models\Wallet;
use ReflectionClass;
use ReflectionException;

trait CanCalculateWallet
{
    /**
     * @return int|float
     */
    public function getBalanceAttribute(): int|float
    {
        if (empty($this->decimal_places) && $this->decimal_places === 0) {
            return $this->raw_balance;
        }

        return (int) $this->raw_balance / pow(10, $this->decimal_places);
    }

    /**
     * @return float
     */
    public function getBalanceFloatAttribute(): float
    {
        return $this->convertToDecimal($this->raw_balance);
    }

    /**
     * Safe check if the balance still enough for creating transactions.
     * If the balance is enough it will return true, else false.
     *
     * @param  float|int|WalletTransaction  $transaction
     * @return bool
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function check(float|int|WalletTransaction $transaction): bool
    {
        if (is_numeric($transaction)) {
            return $this->raw_balance >= $this->convertToWalletInt($transaction);
        }

        if (! (new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class)) {
            throw new Exception('Transaction expects parameter to be a number or a WalletTransaction object.');
        }

        return $this->raw_balance >= $this->convertToWalletInt($transaction->getAmount());
    }

    /**
     * If the Wallet balance is not enough, an exception will be thrown.
     *
     * @param  float|int|WalletTransaction  $transaction
     * @param  array  $metadata
     * @return $this
     *
     * @throws InsufficientBalanceException
     * @throws ReflectionException
     * @throws Exception
     */
    public function pay(float|int|WalletTransaction $transaction, array $metadata = []): static
    {

        if (is_numeric($transaction)) {
            $amount = $this->convertToWalletInt($transaction);
            if ($amount > $this->raw_balance) {
                throw new InsufficientBalanceException('Insufficient balance');
            }

            $this->transaction(amount: $amount, metadata: $metadata);

            $this->decrement('raw_balance', $amount);
            $this->refresh();

            return $this;
        }

        if (! (new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class)) {
            throw new Exception('Transaction expects parameter to be a number or a WalletTransaction object.');
        }

        $amount = $this->convertToWalletInt($transaction->getAmount());

        if ($amount > $this->raw_balance) {
            throw new InsufficientBalanceException('Insufficient balance');
        }

        $this->transaction(amount: $amount, metadata: $metadata, transaction: $transaction);
        $this->decrement('raw_balance', $amount);
        $this->refresh();

        return $this;

    }

    /**
     * This will add an amount to the current wallet balance.
     *
     * @param  float|int|WalletTransaction  $transaction
     * @param  array  $metadata
     * @return $this
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function deposit(float|int|WalletTransaction $transaction, array $metadata = []): static
    {
        if (is_numeric($transaction)) {
            $amount = $this->convertToWalletInt($transaction);
            $this->transaction(amount: $amount, metadata: $metadata, transactionType: TransactionTypeEnum::DEPOSIT);
            $this->increment('raw_balance', $amount);
            $this->refresh();

            return $this;
        }

        if (! (new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class)) {
            throw new Exception('Transaction expects parameter to be a number or a WalletTransaction object.');
        }

        $amount = $this->convertToWalletInt($transaction->getAmount());
        $this->transaction(amount: $amount, metadata: $metadata, transaction: $transaction, transactionType: TransactionTypeEnum::DEPOSIT);
        $this->increment('raw_balance', $amount);
        $this->refresh();

        return $this;

    }

    /**
     * This will withdraw from the user wallet.
     *
     * @param  float|int  $amount
     * @param  array  $metadata
     * @param  float|int  $feeAmount
     * @return $this
     *
     * @throws InsufficientBalanceException
     * @throws ReflectionException
     */
    public function withdraw(float|int $amount, array $metadata = [], float|int $feeAmount = 0): static
    {
        $amount = $this->convertToWalletInt($amount);
        $feeAmount = $this->convertToWalletInt($feeAmount);

        // check to make sure the current wallet has enough balance
        $combinedAmount = $feeAmount + $amount;
        if ($combinedAmount > $this->raw_balance) {
            throw new InsufficientBalanceException('Insufficient balance');
        }

        // Create a transaction history for fees
        $this->fee($feeAmount, $metadata);

        // Create a transaction history for the withdrawal
        $this->transaction(amount: $amount, metadata: $metadata);

        $this->decrement('raw_balance', $amount);
        $this->refresh();

        return $this;
    }

    /**
     * This will transfer the balance amount to other wallet with/out transfer fee
     *
     * @param  float|int  $amount
     * @param  Wallet  $wallet
     * @param  array  $metadata
     * @param  float|int  $feeAmount
     * @return $this
     *
     * @throws InsufficientBalanceException
     * @throws ReflectionException
     * @throws WalletTransferException
     */
    public function transfer(float|int $amount, Wallet $wallet, array $metadata = [], float|int $feeAmount = 0): static
    {
        // check if the wallet destination is the current wallet or not
        if ($this->id === $wallet->id) {
            throw new WalletTransferException('Unable to transfer to the same wallet');
        }
        $amount = is_float($amount) ? $this->convertToWalletInt($amount) : $amount;
        $feeAmount = is_float($feeAmount) ? $feeAmount : $amount;
        if ($feeAmount > 0) {
            // check to make sure the current wallet has enough balance
            $combinedAmount = $feeAmount + $amount;
            if ($combinedAmount > $this->raw_balance) {
                throw new InsufficientBalanceException('Insufficient balance');
            }
        }

        // transfer the amount to the destination wallet
        $wallet->increment('raw_balance', $amount);

        // Create a transaction history for fees
        $this->fee($feeAmount, $metadata);

        // Create a transaction history for the balance wallet transfer
        $this->transaction(amount: $amount, metadata: $metadata);

        $this->decrement('raw_balance', $amount);
        $this->refresh();

        return $this;

    }

    /**
     * Fee can be used if you have a transaction charges against the transaction/processing.
     *
     * @param  float|int|WalletTransaction  $transaction
     * @param  array  $metadata
     * @return $this
     *
     * @throws InsufficientBalanceException
     * @throws ReflectionException
     * @throws Exception
     */
    public function fee(float|int|WalletTransaction $transaction, array $metadata = []): static
    {
        if (is_numeric($transaction)) {
            $amount = $this->convertToWalletInt($transaction);

            if ($amount > $this->raw_balance) {
                throw new InsufficientBalanceException('Insufficient balance');
            }
            $this->transaction(amount: $amount, metadata: $metadata, transactionType: TransactionTypeEnum::FEE);
            $this->decrement('raw_balance', $amount);
            $this->refresh();

            return $this;
        }

        if (! (new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class)) {
            throw new Exception('Transaction expects parameter to be a number or a WalletTransaction object.');
        }

        $amount = $this->convertToWalletInt($transaction->getAmount());

        if ($amount > $this->raw_balance) {
            throw new InsufficientBalanceException('Insufficient balance');
        }

        $this->decrement('raw_balance', $amount);
        $this->refresh();
        $this->transaction(amount: $amount, metadata: $metadata, transaction: $transaction, transactionType: TransactionTypeEnum::FEE);

        return $this;

    }

    /**
     * Record the transaction happens in the wallet.
     *
     * @param  float|int|WalletTransaction  $amount
     * @param  array|null  $metadata
     * @param  WalletTransaction|null  $transaction
     * @param  TransactionTypeEnum|null  $transactionType
     *
     * @throws ReflectionException
     */
    private function transaction(float|int|WalletTransaction $amount, ?array $metadata, ?WalletTransaction $transaction = null, ?TransactionTypeEnum $transactionType = TransactionTypeEnum::PAYMENT): void
    {
        $this->transactions()->create([
            'amount' => $amount,
            ...((! is_null($transaction) && (new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class)) ? ['transaction_id' => (new ReflectionClass($transaction))->getMethod('getKey')->invoke($transaction)] : []),
            ...((! is_null($transaction) && (new ReflectionClass($transaction))->implementsInterface(WalletTransaction::class)) ? ['transaction_type' => get_class($transaction)] : []),
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
     *
     * @param  float|int  $amount
     * @return int
     */
    private function convertToWalletInt(float|int $amount): int
    {
        return $amount * pow(10, $this->decimal_places);
    }

    /**
     * Convert to Decimal number format
     *
     * @param  int  $amount
     * @return float
     */
    private function convertToDecimal(int $amount): float
    {
        return $amount / pow(10, $this->decimal_places);
    }
}
