<?php
require_once __DIR__ . '/../src/db.php';

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
                role TEXT NOT NULL
            )',
            'CREATE TABLE IF NOT EXISTS services (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                price REAL NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )',
            'CREATE TABLE IF NOT EXISTS expenses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                service_id INTEGER NOT NULL,
                amount REAL NOT NULL,
                description TEXT,
                expense_date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(service_id) REFERENCES services(id)
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
                role VARCHAR(50) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            'CREATE TABLE IF NOT EXISTS services (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                price DECIMAL(10,2) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            'CREATE TABLE IF NOT EXISTS expenses (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                service_id INT UNSIGNED NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                description TEXT NULL,
                expense_date DATE NOT NULL,
                FOREIGN KEY(service_id) REFERENCES services(id)
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
                role VARCHAR(50) NOT NULL
            )',
            'CREATE TABLE IF NOT EXISTS services (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                price NUMERIC(10,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )',
            'CREATE TABLE IF NOT EXISTS expenses (
                id SERIAL PRIMARY KEY,
                service_id INT NOT NULL,
                amount NUMERIC(10,2) NOT NULL,
                description TEXT NULL,
                expense_date DATE NOT NULL DEFAULT CURRENT_DATE,
                FOREIGN KEY(service_id) REFERENCES services(id)
            )',
        ];
        break;
    default:
        throw new RuntimeException("Unsupported database driver: {$driver}");
}

foreach ($schemaStatements as $statement) {
    $pdo->exec($statement);
}

$pdo->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    price REAL NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    service_id INTEGER NOT NULL,
    amount REAL NOT NULL,
    description TEXT,
    expense_date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(service_id) REFERENCES services(id)
)');

$existing = $pdo->query('SELECT COUNT(*) as count FROM users')->fetch()['count'] ?? 0;

if ((int) $existing === 0) {
    $users = [
        ['Admin User', 'admin@hostel.local', 'admin123', 'admin'],
        ['Checker User', 'checker@hostel.local', 'checker123', 'checker'],
        ['Delivery User', 'delivery@hostel.local', 'delivery123', 'delivery_person'],
        ['Sanitary Seller', 'sanitary@hostel.local', 'sanitary123', 'sanitary_seller'],
        ['AC Servicer', 'acservice@hostel.local', 'acservice123', 'ac_servicer'],
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

$serviceCount = $pdo->query('SELECT COUNT(*) as count FROM services')->fetch()['count'] ?? 0;

if ((int) $serviceCount === 0) {
    $services = [
        ['Eggs Supply', 4.50],
        ['Chicken Supply', 8.00],
        ['Vegetable Supply', 3.00],
        ['Sanitary Accessories', 5.50],
        ['AC Servicing', 25.00],
    ];

    $stmt = $pdo->prepare('INSERT INTO services (name, price) VALUES (:name, :price)');

    foreach ($services as [$name, $price]) {
        $stmt->execute([
            'name' => $name,
            'price' => $price,
        ]);
    }

    echo "Seeded default services.\n";
} else {
    echo "Services already exist. No changes made.\n";
}

$expenseCount = $pdo->query('SELECT COUNT(*) as count FROM expenses')->fetch()['count'] ?? 0;

if ((int) $expenseCount === 0) {
    $expenseSeed = [
        ['Eggs Supply', 180.00, 'Weekly egg delivery'],
        ['Chicken Supply', 240.00, 'Fresh chicken stock'],
        ['Vegetable Supply', 95.00, 'Mixed vegetables'],
        ['Sanitary Accessories', 60.00, 'Monthly cleaning supplies'],
        ['AC Servicing', 150.00, 'Quarterly maintenance check'],
    ];

    $lookup = $pdo->prepare('SELECT id FROM services WHERE name = :name');
    $insertExpense = $pdo->prepare('INSERT INTO expenses (service_id, amount, description, expense_date) VALUES (:service_id, :amount, :description, :expense_date)');

    foreach ($expenseSeed as [$serviceName, $amount, $description]) {
        $lookup->execute(['name' => $serviceName]);
        $serviceId = $lookup->fetchColumn();

        if ($serviceId) {
            $insertExpense->execute([
                'service_id' => $serviceId,
                'amount' => $amount,
                'description' => $description,
                'expense_date' => date('Y-m-d'),
            ]);
        }
    }

    echo "Seeded sample expenses.\n";
} else {
    echo "Expenses already exist. No changes made.\n";
}
