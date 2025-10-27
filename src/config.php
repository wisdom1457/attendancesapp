<?php

return [
    'db_path' => __DIR__ . '/../storage/app.sqlite',
    'session_name' => 'hostel_mgmt_session',
    'sslcommerz' => [
        'store_id' => getenv('SSLC_STORE_ID') ?: 'testbox',
        'store_password' => getenv('SSLC_STORE_PASSWORD') ?: 'qwerty',
        'sandbox' => getenv('SSLC_SANDBOX') !== 'false',
        'currency' => 'BDT',
        'base_url' => rtrim(getenv('APP_BASE_URL') ?: 'http://localhost:8000', '/'),
    ],
];
