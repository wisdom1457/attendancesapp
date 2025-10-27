<?php

$rootDir = __DIR__ . '/..';
$envFile = $rootDir . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }
        if (!str_contains($trimmed, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $trimmed, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name === '') {
            continue;
        }

        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
        if (!array_key_exists($name, $_SERVER)) {
            $_SERVER[$name] = $value;
        }
        putenv("{$name}={$value}");
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return $value;
    }
}

return [
    'session_name' => env('SESSION_NAME', 'hostel_mgmt_session'),
    'db' => [
        'driver' => env('DB_DRIVER', 'sqlite'),
        'database' => env('DB_DATABASE', $rootDir . '/storage/app.sqlite'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'username' => env('DB_USERNAME', ''),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'dsn' => env('DB_DSN'),
    ],
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
