<?php

namespace MarJose123\Pitaka\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MarJose123\Pitaka\Pitaka
 */
class Pitaka extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MarJose123\Pitaka\Pitaka::class;
    }
}
