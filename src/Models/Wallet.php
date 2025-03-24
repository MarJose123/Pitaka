<?php

namespace MarJose123\Pitaka\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Wallet extends Model
{
    use HasUuids;

    protected $fillable = [
        'owner_id',
        'owner_type',
        'name',
        'slug',
        'balance',
        'decimal_places',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'balance' => 'float',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo('owner');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
