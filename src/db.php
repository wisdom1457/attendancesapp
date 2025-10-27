<?php

function get_db(): PDO
{
    static $pdo;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require __DIR__ . '/config.php';
    $dbConfig = $config['db'];

    $driver = strtolower((string) ($dbConfig['driver'] ?? 'sqlite'));
    $dsn = $dbConfig['dsn'] ?? null;
    $username = $dbConfig['username'] ?? null;
    $password = $dbConfig['password'] ?? null;
    $options = [];

    if ($driver === 'sqlite') {
        if ($dsn === null) {
            $databasePath = $dbConfig['database'] ?? (__DIR__ . '/../storage/app.sqlite');
            $isAbsolute = str_starts_with($databasePath, '/')
                || str_starts_with($databasePath, '\\')
                || (strlen($databasePath) > 1 && ctype_alpha($databasePath[0]) && $databasePath[1] === ':');

            if ($databasePath !== '' && !$isAbsolute) {
                $databasePath = dirname(__DIR__) . '/' . ltrim($databasePath, '\\/');
            }
            if (!file_exists(dirname($databasePath))) {
                mkdir(dirname($databasePath), 0777, true);
            }
            $dsn = 'sqlite:' . $databasePath;
        }
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

    $pdo = new PDO($dsn, $username, $password, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
}
