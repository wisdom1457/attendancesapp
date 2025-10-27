<?php
require_once __DIR__ . '/../src/db.php';

$pdo = get_db();

$pdo->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL
)');

$existing = $pdo->query('SELECT COUNT(*) as count FROM users')->fetch()['count'] ?? 0;

if ((int) $existing === 0) {
    $users = [
        ['Admin User', 'admin@hostel.local', 'admin123', 'admin'],
        ['Checker User', 'checker@hostel.local', 'checker123', 'checker'],
        ['Delivery User', 'delivery@hostel.local', 'delivery123', 'delivery_person'],
    ];

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');

    foreach ($users as [$name, $email, $password, $role]) {
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);
    }

    echo "Seeded default users.\n";
} else {
    echo "Users already exist. No changes made.\n";
}
