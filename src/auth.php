<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $config = require __DIR__ . '/config.php';
    $sessionName = $config['session_name'] ?? 'attendance_app_session';

    session_name($sessionName);
    session_start();
}

function attempt_login(string $email, string $password): bool
{
    $normalizedEmail = strtolower(trim($email));
    if ($normalizedEmail === '' || $password === '') {
        return false;
    }

    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $normalizedEmail]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    start_session();
    $_SESSION['user_id'] = (int) $user['id'];

    return true;
}

function current_user(): ?array
{
    start_session();

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = :id LIMIT 1');
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
