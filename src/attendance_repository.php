<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function fetch_employees(): array
{
    $pdo = get_db();
    $stmt = $pdo->query('SELECT id, name, email, department, job_title, employee_code, fingerprint_hint, created_at FROM employees ORDER BY name ASC');

    return $stmt->fetchAll() ?: [];
}

function count_employees(): int
{
    $pdo = get_db();
    $count = $pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();

    return $count ? (int) $count : 0;
}

function find_employee_by_code(string $code): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM employees WHERE employee_code = :code LIMIT 1');
    $stmt->execute(['code' => strtoupper(trim($code))]);
    $employee = $stmt->fetch();

    return $employee ?: null;
}

function employee_exists_by_email(string $email): bool
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT 1 FROM employees WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => strtolower(trim($email))]);

    return (bool) $stmt->fetchColumn();
}

function generate_employee_code(PDO $pdo): string
{
    do {
        $code = 'EMP-' . random_int(10000, 99999);
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM employees WHERE employee_code = :code');
        $stmt->execute(['code' => $code]);
        $exists = (int) $stmt->fetchColumn() > 0;
    } while ($exists);

    return $code;
}

function create_employee(string $name, string $email, string $department, string $jobTitle, string $fingerprintPhrase): array
{
    $name = trim($name);
    $email = strtolower(trim($email));
    $department = trim($department);
    $jobTitle = trim($jobTitle);
    $fingerprintPhrase = trim($fingerprintPhrase);

    if ($name === '' || $email === '' || $fingerprintPhrase === '') {
        throw new InvalidArgumentException('Name, email, and fingerprint phrase are required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Please provide a valid email address.');
    }

    $pdo = get_db();

    if (employee_exists_by_email($email)) {
        throw new InvalidArgumentException('An employee with this email already exists.');
    }

    $fingerprintHash = password_hash($fingerprintPhrase, PASSWORD_DEFAULT);
    $fingerprintHint = strtoupper(substr(hash('sha256', $fingerprintPhrase), 0, 8));
    $employeeCode = generate_employee_code($pdo);

    $stmt = $pdo->prepare('INSERT INTO employees (name, email, department, job_title, employee_code, fingerprint_hash, fingerprint_hint)
        VALUES (:name, :email, :department, :job_title, :employee_code, :fingerprint_hash, :fingerprint_hint)');

    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'department' => $department,
        'job_title' => $jobTitle,
        'employee_code' => $employeeCode,
        'fingerprint_hash' => $fingerprintHash,
        'fingerprint_hint' => $fingerprintHint,
    ]);

    $id = (int) $pdo->lastInsertId();

    $lookup = $pdo->prepare('SELECT id, name, email, department, job_title, employee_code, fingerprint_hint, created_at FROM employees WHERE id = :id');
    $lookup->execute(['id' => $id]);

    return $lookup->fetch();
}

function verify_fingerprint(string $input, string $hash): bool
{
    return password_verify($input, $hash);
}

function record_attendance(int $employeeId, string $status, string $deviceLabel, ?string $location, ?string $notes): void
{
    $validStatuses = ['check_in', 'check_out'];
    if (!in_array($status, $validStatuses, true)) {
        throw new InvalidArgumentException('Invalid attendance status.');
    }

    $pdo = get_db();
    $stmt = $pdo->prepare('INSERT INTO attendance_logs (employee_id, status, device_label, location, notes)
        VALUES (:employee_id, :status, :device_label, :location, :notes)');
    $stmt->execute([
        'employee_id' => $employeeId,
        'status' => $status,
        'device_label' => trim($deviceLabel) ?: 'Biometric Scanner',
        'location' => $location !== null && trim($location) !== '' ? trim($location) : null,
        'notes' => $notes !== null && trim($notes) !== '' ? trim($notes) : null,
    ]);
}

function fetch_recent_attendance(int $limit = 5): array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT logs.id, logs.status, logs.captured_at, logs.device_label, logs.location, employees.name, employees.department, employees.job_title
        FROM attendance_logs AS logs
        INNER JOIN employees ON logs.employee_id = employees.id
        ORDER BY logs.captured_at DESC, logs.id DESC
        LIMIT :limit');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll() ?: [];
}

function fetch_attendance_logs(?string $date = null, int $limit = 100): array
{
    $pdo = get_db();

    if ($date) {
        $stmt = $pdo->prepare('SELECT logs.id, logs.status, logs.captured_at, logs.device_label, logs.location, logs.notes,
                employees.name, employees.department, employees.job_title, employees.employee_code
            FROM attendance_logs AS logs
            INNER JOIN employees ON logs.employee_id = employees.id
            WHERE DATE(logs.captured_at) = :date
            ORDER BY logs.captured_at DESC, logs.id DESC
            LIMIT :limit');
        $stmt->bindValue(':date', $date);
    } else {
        $stmt = $pdo->prepare('SELECT logs.id, logs.status, logs.captured_at, logs.device_label, logs.location, logs.notes,
                employees.name, employees.department, employees.job_title, employees.employee_code
            FROM attendance_logs AS logs
            INNER JOIN employees ON logs.employee_id = employees.id
            ORDER BY logs.captured_at DESC, logs.id DESC
            LIMIT :limit');
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll() ?: [];
}

function attendance_summary_for_date(string $date): array
{
    $pdo = get_db();

    $stmt = $pdo->prepare('SELECT status, COUNT(*) as total FROM attendance_logs WHERE DATE(captured_at) = :date GROUP BY status');
    $stmt->execute(['date' => $date]);
    $rows = $stmt->fetchAll();

    $summary = [
        'check_in' => 0,
        'check_out' => 0,
    ];

    foreach ($rows as $row) {
        $status = $row['status'];
        if (isset($summary[$status])) {
            $summary[$status] = (int) $row['total'];
        }
    }

    $uniqueStmt = $pdo->prepare('SELECT COUNT(DISTINCT employee_id) FROM attendance_logs WHERE DATE(captured_at) = :date');
    $uniqueStmt->execute(['date' => $date]);
    $summary['unique_employees'] = (int) ($uniqueStmt->fetchColumn() ?: 0);

    return $summary;
}
