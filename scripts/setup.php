<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/attendance_repository.php';

$pdo = get_db();
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

switch ($driver) {
    case 'sqlite':
        $schemaStatements = [
            'CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )',
            'CREATE TABLE IF NOT EXISTS employees (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                department TEXT,
                job_title TEXT,
                employee_code TEXT NOT NULL UNIQUE,
                fingerprint_hash TEXT NOT NULL,
                fingerprint_hint TEXT NOT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )',
            'CREATE TABLE IF NOT EXISTS attendance_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                employee_id INTEGER NOT NULL,
                status TEXT NOT NULL,
                device_label TEXT NOT NULL,
                location TEXT,
                notes TEXT,
                captured_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(employee_id) REFERENCES employees(id)
            )',
        ];
        break;
    case 'mysql':
        $schemaStatements = [
            'CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            'CREATE TABLE IF NOT EXISTS employees (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                department VARCHAR(255) NULL,
                job_title VARCHAR(255) NULL,
                employee_code VARCHAR(32) NOT NULL UNIQUE,
                fingerprint_hash VARCHAR(255) NOT NULL,
                fingerprint_hint VARCHAR(32) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            'CREATE TABLE IF NOT EXISTS attendance_logs (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                employee_id INT UNSIGNED NOT NULL,
                status VARCHAR(20) NOT NULL,
                device_label VARCHAR(255) NOT NULL,
                location VARCHAR(255) NULL,
                notes TEXT NULL,
                captured_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_attendance_employee FOREIGN KEY(employee_id) REFERENCES employees(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        ];
        break;
    case 'pgsql':
        $schemaStatements = [
            'CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )',
            'CREATE TABLE IF NOT EXISTS employees (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                department VARCHAR(255),
                job_title VARCHAR(255),
                employee_code VARCHAR(32) NOT NULL UNIQUE,
                fingerprint_hash VARCHAR(255) NOT NULL,
                fingerprint_hint VARCHAR(32) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )',
            'CREATE TABLE IF NOT EXISTS attendance_logs (
                id SERIAL PRIMARY KEY,
                employee_id INT NOT NULL REFERENCES employees(id),
                status VARCHAR(20) NOT NULL,
                device_label VARCHAR(255) NOT NULL,
                location VARCHAR(255),
                notes TEXT,
                captured_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )',
        ];
        break;
    default:
        throw new RuntimeException("Unsupported database driver: {$driver}");
}

foreach ($schemaStatements as $statement) {
    $pdo->exec($statement);
}

echo "Database schema ready.\n";

$userCount = (int) ($pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() ?: 0);
if ($userCount === 0) {
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)');
    $stmt->execute([
        'name' => 'Administrator',
        'email' => 'admin@attendance.local',
        'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
    ]);
    echo "Seeded default administrator (admin@attendance.local / admin123).\n";
} else {
    echo "Users already seeded.\n";
}

$employeeCount = (int) ($pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn() ?: 0);
if ($employeeCount === 0) {
    $seedEmployees = [
        ['Amelia Hart', 'amelia.hart@example.com', 'Operations', 'Operations Manager', 'fingerprint-amelia'],
        ['Malik Green', 'malik.green@example.com', 'Facilities', 'Facilities Supervisor', 'fingerprint-malik'],
        ['Priya Das', 'priya.das@example.com', 'Human Resources', 'HR Specialist', 'fingerprint-priya'],
    ];

    $createdEmployees = [];
    foreach ($seedEmployees as [$name, $email, $department, $jobTitle, $fingerprintPhrase]) {
        $createdEmployees[] = create_employee($name, $email, $department, $jobTitle, $fingerprintPhrase);
    }

    echo "Seeded sample employees with biometric templates.\n";

    $statusOptions = ['check_in', 'check_out'];
    $deviceLabels = ['East Wing Scanner', 'Main Lobby Pad'];
    $locations = ['Headquarters', 'Remote Office'];

    foreach ($createdEmployees as $index => $employee) {
        foreach ($statusOptions as $statusIndex => $status) {
            $timestamp = (new DateTimeImmutable('-' . (2 - $statusIndex) . ' hours'));
            $device = $deviceLabels[$index % count($deviceLabels)];
            $location = $locations[$index % count($locations)];

            $stmt = $pdo->prepare('INSERT INTO attendance_logs (employee_id, status, device_label, location, notes, captured_at)
                VALUES (:employee_id, :status, :device_label, :location, :notes, :captured_at)');
            $stmt->execute([
                'employee_id' => $employee['id'],
                'status' => $status,
                'device_label' => $device,
                'location' => $location,
                'notes' => $status === 'check_in' ? 'Automated enrollment log' : 'Scheduled departure',
                'captured_at' => $timestamp->format('Y-m-d H:i:s'),
            ]);
        }
    }

    echo "Seeded sample attendance logs.\n";
} else {
    echo "Employees already exist. No additional data seeded.\n";
}
