<?php

return [
    'db_path' => __DIR__ . '/../storage/app.sqlite',
    'session_name' => 'hostel_mgmt_session',
    'sslcommerz' => [
        'store_id' => getenv('SSLCOMMERZ_STORE_ID') ?: '',
        'store_password' => getenv('SSLCOMMERZ_STORE_PASSWORD') ?: getenv('SSLCOMMERZ_STORE_PASSWD') ?: '',
        'default_currency' => getenv('SSLCOMMERZ_CURRENCY') ?: 'BDT',
        'sandbox_mode' => filter_var(getenv('SSLCOMMERZ_SANDBOX') ?? 'true', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
    ],
];
