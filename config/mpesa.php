<?php

return [
    /*
    |--------------------------------------------------------------------------
    | M-Pesa API Configuration
    |--------------------------------------------------------------------------
    |
    | Set these values in your .env file:
    |   MPESA_CONSUMER_KEY=your_consumer_key
    |   MPESA_CONSUMER_SECRET=your_consumer_secret
    |   MPESA_PASS_KEY=your_pass_key
    |   MPESA_SHORT_CODE=174379
    |   MPESA_ENV=sandbox
    |
    */

    'consumer_key' => env('MPESA_CONSUMER_KEY', ''),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),
    'pass_key' => env('MPESA_PASS_KEY', ''),
    'short_code' => env('MPESA_SHORT_CODE', '174379'),
    'env' => env('MPESA_ENV', 'sandbox'),
];
