<?php

require_once __DIR__ . '/db.php';

function start_session(): void
{
    $config = require __DIR__ . '/config.php';
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name($config['session_name']);
        session_start();
    }
}

function attempt_login(string $email, string $password): bool
{
    $normalizedEmail = strtolower(trim($email));

    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $normalizedEmail]);
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    start_session();
    $_SESSION['user_id'] = $user['id'];

    return true;
}

function register_user(string $name, string $email, string $password, string $role): array
{
    $allowedRoles = ['delivery_person', 'sanitary_seller', 'ac_servicer', 'checker'];

    if (!in_array($role, $allowedRoles, true)) {
        throw new InvalidArgumentException('Role is not allowed for self-registration.');
    }

    $pdo = get_db();
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $role,
    ]);

    $id = (int) $pdo->lastInsertId();

    return [
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'role' => $role,
    ];
}

function current_user(): ?array
{
    start_session();

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        header('Location: /index.php');
        exit;
    }

    return $user;
}

function require_role(array $user, array $roles): void
{
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        echo 'Access denied';
        exit;
    }
}

function logout(): void
{
    start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
