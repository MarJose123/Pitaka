<?php

namespace MarJose123\Pitaka\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'wallet_id',
        'transaction_id',
        'transaction_type',
        'amount',
        'running_balance',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'int',
        'running_balance' => 'int',
    ];
}
