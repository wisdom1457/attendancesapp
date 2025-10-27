<?php
declare(strict_types=1);

$rootDir = dirname(__DIR__);
$envPath = $rootDir . '/.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
            continue;
        }

        [$name, $value] = array_map('trim', explode('=', $trimmed, 2));
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
    'session_name' => env('SESSION_NAME', 'attendance_app_session'),
    'db' => [
        'driver' => strtolower((string) env('DB_DRIVER', 'sqlite')),
        'database' => env('DB_DATABASE', $rootDir . '/storage/app.sqlite'),
        'dsn' => env('DB_DSN'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
    ],
];
