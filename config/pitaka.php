<?php

return [
    /**
     * --------------------------------------------------------------
     *  Wallet Migration
     * --------------------------------------------------------------
     * This will be used for the Wallet Table
     */
    'wallet_table' => [
        'decimal_places' => 2,
    ],

    /**
     *  -------------------------------------------------------------------
     *  Default User Wallet
     *  -------------------------------------------------------------------
     */
    'user' => [
        'wallet' => null, // Add your default wallet name here. This will be used if you don't provide a wallet name when calling `$user->wallet()`
    ],

];
