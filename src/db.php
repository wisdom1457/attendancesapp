<?php
declare(strict_types=1);

function get_db(): PDO
{
    static $pdo;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require __DIR__ . '/config.php';
    $dbConfig = $config['db'] ?? [];

    $driver = $dbConfig['driver'] ?? 'sqlite';
    $dsn = $dbConfig['dsn'] ?? null;
    $username = $dbConfig['username'] ?? null;
    $password = $dbConfig['password'] ?? null;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    if ($driver === 'sqlite') {
        $databasePath = $dbConfig['database'] ?? (dirname(__DIR__) . '/storage/app.sqlite');
        $isAbsolute = str_starts_with($databasePath, DIRECTORY_SEPARATOR)
            || (strlen($databasePath) > 1 && ctype_alpha($databasePath[0]) && $databasePath[1] === ':')
            || str_contains($databasePath, '://');

        if (!$isAbsolute) {
            $databasePath = dirname(__DIR__) . '/' . ltrim($databasePath, '/');
        }

        if (!is_dir(dirname($databasePath))) {
            mkdir(dirname($databasePath), 0777, true);
        }

        $dsn = $dsn ?: 'sqlite:' . $databasePath;
        $username = null;
        $password = null;
    } elseif ($dsn === null) {
        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? null;
        $database = $dbConfig['database'] ?? '';
        $charset = $dbConfig['charset'] ?? 'utf8mb4';

        if ($driver === 'mysql') {
            $portPart = $port ? ";port={$port}" : '';
            $dsn = "mysql:host={$host}{$portPart};dbname={$database};charset={$charset}";
        } elseif ($driver === 'pgsql') {
            $portPart = $port ? ";port={$port}" : '';
            $dsn = "pgsql:host={$host}{$portPart};dbname={$database}";
        } else {
            throw new RuntimeException("Unsupported database driver: {$driver}");
        }
    }

    $pdo = new PDO((string) $dsn, $username, $password, $options);

    return $pdo;
}
