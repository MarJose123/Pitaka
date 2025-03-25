<?php

namespace MarJose123\Pitaka\Contract;

interface WalletTransaction
{
    public function getPrice(): float;

    public function getPrimaryKey(): string;
}
